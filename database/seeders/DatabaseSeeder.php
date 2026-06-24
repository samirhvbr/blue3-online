<?php

namespace Database\Seeders;

use App\Enums\ComponentStatus;
use App\Models\Component;
use App\Models\ComponentGroup;
use App\Models\Incident;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuário admin de bootstrap. ⚠️ Trocar a senha no 1º acesso em produção.
        User::updateOrCreate(
            ['email' => 'samir@blue3.com.br'],
            ['name' => 'Samir', 'password' => Hash::make('blue3samir123')],
        );

        // Grupos + componentes (espelham o conjunto que estava em mock).
        $pops = ComponentGroup::updateOrCreate(['name' => 'POPs'], ['position' => 1]);
        $servicos = ComponentGroup::updateOrCreate(['name' => 'Serviços'], ['position' => 2]);

        $components = [
            [$pops, 'POP Centro'],
            [$pops, 'POP Zona Sul'],
            [$pops, 'POP Industrial'],
            [$pops, 'POP Litoral'],
            [$servicos, 'Site institucional (blue3.com.br)'],
            [$servicos, 'Central do Assinante'],
            [$servicos, 'DNS recursivo'],
            [$servicos, 'Servidor de e-mail'],
            [$servicos, 'Backbone / Trânsito IP'],
        ];

        foreach ($components as $i => [$group, $name]) {
            Component::updateOrCreate(
                ['component_group_id' => $group->id, 'name' => $name],
                ['status' => ComponentStatus::Operational, 'position' => $i],
            );
        }

        // Incidentes de exemplo (resolvidos) — alimentam histórico e timeline.
        $litoral = Component::where('name', 'POP Litoral')->first();
        $dns = Component::where('name', 'DNS recursivo')->first();
        $backbone = Component::where('name', 'Backbone / Trânsito IP')->first();
        $now = Carbon::now();

        $incidents = [
            [
                'title' => 'Perda de pacotes no POP Litoral',
                'detail' => 'Oscilação no enlace de trânsito IP do POP Litoral causou perda de pacotes e latência elevada para parte dos assinantes. Mitigado com remanejamento de rota; enlace normalizado.',
                'impact' => ComponentStatus::PartialOutage,
                'component_id' => $litoral?->id,
                'started_at' => $now->copy()->subDays(6)->setTime(14, 3),
                'resolved_at' => $now->copy()->subDays(6)->setTime(14, 45),
            ],
            [
                'title' => 'Lentidão no DNS recursivo',
                'detail' => 'Aumento do tempo de resposta nos resolvedores recursivos após pico de consultas. Capacidade ampliada e cache aquecido.',
                'impact' => ComponentStatus::Degraded,
                'component_id' => $dns?->id,
                'started_at' => $now->copy()->subDays(17)->setTime(9, 21),
                'resolved_at' => $now->copy()->subDays(17)->setTime(9, 38),
            ],
            [
                'title' => 'Manutenção programada no Backbone',
                'detail' => 'Upgrade de firmware nos roteadores de núcleo em janela de baixo tráfego. Sem impacto perceptível aos assinantes.',
                'impact' => ComponentStatus::Maintenance,
                'component_id' => $backbone?->id,
                'started_at' => $now->copy()->subDays(26)->setTime(2, 0),
                'resolved_at' => $now->copy()->subDays(26)->setTime(3, 10),
            ],
        ];

        foreach ($incidents as $data) {
            Incident::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}
