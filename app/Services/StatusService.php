<?php

namespace App\Services;

use App\Enums\ComponentStatus;
use App\Models\ComponentGroup;
use App\Models\Incident;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Monta os dados da página de status a partir do banco.
 *
 * - Status atual de cada componente: definido no admin (campo `status`).
 * - Histórico de 90 dias e uptime: derivados dos incidentes que impactaram o
 *   componente (incidente sem componente = global, afeta todos).
 * - Quando houver sondas, elas passam a gravar medições/incidentes e este
 *   serviço continua igual — só a origem dos dados muda.
 */
class StatusService
{
    private const HISTORY_DAYS = 90;

    private ?array $groups = null;
    private ?Collection $incidents = null;

    public function groups(): array
    {
        if ($this->groups !== null) {
            return $this->groups;
        }

        $incidents = $this->incidentRecords();

        return $this->groups = ComponentGroup::with('components')
            ->orderBy('position')
            ->get()
            ->map(fn (ComponentGroup $group) => [
                'name' => $group->name,
                'components' => $group->components->map(function ($component) use ($incidents) {
                    $history = $this->history($component->id, $incidents);

                    return [
                        'name' => $component->name,
                        'status' => $component->status,
                        'history' => $history,
                        'uptime' => $this->uptime($history),
                    ];
                })->all(),
            ])->all();
    }

    /** Pior status atual entre todos os componentes — alimenta o banner geral. */
    public function overall(): ComponentStatus
    {
        $worst = ComponentStatus::Operational;

        foreach ($this->groups() as $group) {
            foreach ($group['components'] as $component) {
                if ($component['status']->severity() > $worst->severity()) {
                    $worst = $component['status'];
                }
            }
        }

        return $worst;
    }

    /** Incidentes recentes (mais novos primeiro) para a timeline pública. */
    public function incidents(): array
    {
        return $this->incidentRecords()
            ->take(10)
            ->map(function (Incident $incident) {
                $resolved = $incident->isResolved();
                $maintenance = $incident->impact === ComponentStatus::Maintenance;

                return [
                    'title' => $incident->title,
                    'detail' => $incident->detail,
                    'date' => $this->dateLabel($incident->started_at),
                    'duration' => $this->durationLabel($incident),
                    'badge' => $resolved ? ($maintenance ? ComponentStatus::Maintenance : ComponentStatus::Operational) : $incident->impact,
                    'badge_label' => $resolved ? ($maintenance ? 'Concluído' : 'Resolvido') : 'Em andamento',
                ];
            })->values()->all();
    }

    public function lastUpdatedLabel(): string
    {
        return $this->dateLabel(Carbon::now());
    }

    private function incidentRecords(): Collection
    {
        return $this->incidents ??= Incident::query()->orderByDesc('started_at')->get();
    }

    /** Histórico de 90 dias do componente, derivado dos incidentes que o impactaram. */
    private function history(int $componentId, Collection $incidents): array
    {
        $relevant = $incidents->filter(
            fn (Incident $i) => $i->component_id === null || $i->component_id === $componentId
        );

        $start = Carbon::now()->subDays(self::HISTORY_DAYS - 1)->startOfDay();
        $days = [];

        for ($i = 0; $i < self::HISTORY_DAYS; $i++) {
            $dayStart = $start->copy()->addDays($i);
            $dayEnd = $dayStart->copy()->endOfDay();

            $status = ComponentStatus::Operational;
            foreach ($relevant as $incident) {
                $from = $incident->started_at;
                $to = $incident->resolved_at ?? Carbon::now();

                if ($from->lte($dayEnd) && $to->gte($dayStart) && $incident->impact->severity() > $status->severity()) {
                    $status = $incident->impact;
                }
            }

            $days[] = ['date' => $dayStart, 'status' => $status];
        }

        return $days;
    }

    private function uptime(array $history): float
    {
        $downtime = (new Collection($history))->sum(fn (array $day) => $day['status']->downtimeWeight());

        return round((1 - $downtime / count($history)) * 100, 2);
    }

    private function dateLabel(Carbon $date): string
    {
        $months = [1 => 'jan.', 'fev.', 'mar.', 'abr.', 'mai.', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.'];

        return sprintf(
            '%s %s %s, %s',
            $date->format('d'),
            $months[(int) $date->format('n')],
            $date->format('Y'),
            $date->format('H:i')
        );
    }

    private function durationLabel(Incident $incident): string
    {
        if (! $incident->isResolved()) {
            return 'em andamento';
        }

        $minutes = (int) round($incident->started_at->diffInMinutes($incident->resolved_at));

        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $m ? "{$h} h {$m} min" : "{$h} h";
    }
}
