<?php

namespace App\Services;

use App\Enums\ComponentStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Fonte dos dados da página de status.
 *
 * ATENÇÃO: dados MOCK. Quando as sondas externas estiverem reportando para o
 * banco, trocar `groups()`/`incidents()` por leitura real (medições agregadas
 * por componente). A camada de view não muda — só a origem dos dados.
 */
class StatusService
{
    private const HISTORY_DAYS = 90;

    private ?array $groups = null;

    /**
     * Componentes agrupados (POPs e serviços), cada um com status atual,
     * uptime e histórico dos últimos 90 dias.
     */
    public function groups(): array
    {
        return $this->groups ??= [
            [
                'name' => 'POPs',
                'components' => [
                    $this->component('POP Centro'),
                    $this->component('POP Zona Sul', [40 => ComponentStatus::Degraded]),
                    $this->component('POP Industrial'),
                    $this->component('POP Litoral', [
                        83 => ComponentStatus::PartialOutage,
                        82 => ComponentStatus::Degraded,
                    ]),
                ],
            ],
            [
                'name' => 'Serviços',
                'components' => [
                    $this->component('Site institucional (blue3.com.br)', [12 => ComponentStatus::Degraded]),
                    $this->component('Central do Assinante'),
                    $this->component('DNS recursivo', [72 => ComponentStatus::Degraded]),
                    $this->component('Servidor de e-mail', [55 => ComponentStatus::Degraded]),
                    $this->component('Backbone / Trânsito IP', [63 => ComponentStatus::Maintenance]),
                ],
            ],
        ];
    }

    /** Pior status entre todos os componentes — alimenta o banner geral. */
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

    /** Incidentes recentes (resolvidos/concluídos). */
    public function incidents(): array
    {
        return [
            [
                'title' => 'Perda de pacotes no POP Litoral',
                'detail' => 'Oscilação no enlace de trânsito IP do POP Litoral causou perda de pacotes e latência elevada para parte dos assinantes. Mitigado com remanejamento de rota; enlace normalizado.',
                'date' => '18 jun. 2026, 14:03',
                'duration' => '42 min',
                'badge' => ComponentStatus::Operational,
                'badge_label' => 'Resolvido',
            ],
            [
                'title' => 'Lentidão no DNS recursivo',
                'detail' => 'Aumento do tempo de resposta nos resolvedores recursivos após pico de consultas. Capacidade ampliada e cache aquecido.',
                'date' => '07 jun. 2026, 09:21',
                'duration' => '17 min',
                'badge' => ComponentStatus::Operational,
                'badge_label' => 'Resolvido',
            ],
            [
                'title' => 'Manutenção programada no Backbone',
                'detail' => 'Upgrade de firmware nos roteadores de núcleo em janela de baixo tráfego. Sem impacto perceptível aos assinantes.',
                'date' => '29 mai. 2026, 02:00',
                'duration' => '1 h 10 min',
                'badge' => ComponentStatus::Maintenance,
                'badge_label' => 'Concluído',
            ],
        ];
    }

    /** Carimbo "Atualizado em ..." em pt-BR, sem depender de locale do sistema. */
    public function lastUpdatedLabel(): string
    {
        $now = Carbon::now();
        $months = [1 => 'jan.', 'fev.', 'mar.', 'abr.', 'mai.', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.'];

        return sprintf(
            '%s %s %s, %s',
            $now->format('d'),
            $months[(int) $now->format('n')],
            $now->format('Y'),
            $now->format('H:i')
        );
    }

    /**
     * Monta um componente: status atual (último dia do histórico), histórico
     * de 90 dias e uptime calculado a partir dele.
     *
     * @param  array<int,ComponentStatus>  $badDays  offset (0 = 90 dias atrás) => status
     */
    private function component(string $name, array $badDays = []): array
    {
        $history = $this->history($badDays);

        return [
            'name' => $name,
            'status' => end($history)['status'],
            'history' => $history,
            'uptime' => $this->uptime($history),
        ];
    }

    /** @param array<int,ComponentStatus> $badDays */
    private function history(array $badDays): array
    {
        $start = Carbon::now()->subDays(self::HISTORY_DAYS - 1)->startOfDay();
        $days = [];

        for ($i = 0; $i < self::HISTORY_DAYS; $i++) {
            $days[] = [
                'date' => $start->copy()->addDays($i),
                'status' => $badDays[$i] ?? ComponentStatus::Operational,
            ];
        }

        return $days;
    }

    /** Uptime % ponderado pela severidade de cada dia. */
    private function uptime(array $history): float
    {
        $downtime = (new Collection($history))
            ->sum(fn (array $day) => $day['status']->downtimeWeight());

        return round((1 - $downtime / count($history)) * 100, 2);
    }
}
