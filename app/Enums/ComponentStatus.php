<?php

namespace App\Enums;

/**
 * Estado de um componente monitorado (POP ou serviço) na página de status.
 * O `value` também é usado como sufixo das classes CSS `st--{value}`.
 */
enum ComponentStatus: string
{
    case Operational = 'operational';
    case Degraded = 'degraded';
    case PartialOutage = 'partial_outage';
    case MajorOutage = 'major_outage';
    case Maintenance = 'maintenance';

    /** Rótulo curto para a pílula de status. */
    public function label(): string
    {
        return match ($this) {
            self::Operational => 'Operacional',
            self::Degraded => 'Degradado',
            self::PartialOutage => 'Interrupção parcial',
            self::MajorOutage => 'Fora do ar',
            self::Maintenance => 'Em manutenção',
        };
    }

    /** Frase do banner geral, no topo da página. */
    public function headline(): string
    {
        return match ($this) {
            self::Operational => 'Todos os sistemas operacionais',
            self::Degraded => 'Degradação de desempenho em alguns sistemas',
            self::PartialOutage => 'Interrupção parcial de serviço',
            self::MajorOutage => 'Interrupção grave de serviço',
            self::Maintenance => 'Manutenção em andamento',
        };
    }

    /** Severidade para escolher o "pior" status de um conjunto. */
    public function severity(): int
    {
        return match ($this) {
            self::Operational => 0,
            self::Maintenance => 1,
            self::Degraded => 2,
            self::PartialOutage => 3,
            self::MajorOutage => 4,
        };
    }

    /** Fração de indisponibilidade do dia, para o cálculo de uptime. */
    public function downtimeWeight(): float
    {
        return match ($this) {
            self::Operational, self::Maintenance => 0.0,
            self::Degraded => 0.15,
            self::PartialOutage => 0.40,
            self::MajorOutage => 0.80,
        };
    }
}
