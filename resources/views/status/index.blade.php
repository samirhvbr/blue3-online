<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status — Blue3</title>
    <meta name="description" content="Status em tempo real dos serviços e POPs da Blue3.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f8fa] text-slate-800 antialiased">
    <div class="mx-auto max-w-3xl px-4 py-10">

        {{-- Cabeçalho --}}
        <header class="mb-8 flex items-center justify-between">
            <div class="flex items-baseline gap-2">
                <span class="text-xl font-bold tracking-tight text-[#0b63ce]">Blue3</span>
                <span class="text-xl font-light text-slate-400">Status</span>
            </div>
            <a href="https://blue3.com.br" class="text-sm text-slate-500 transition hover:text-slate-700">
                blue3.com.br&nbsp;↗
            </a>
        </header>

        {{-- Banner geral --}}
        <section class="st--{{ $overall->value }} mb-8 rounded-xl border border-slate-200 bg-white px-6 py-5 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="st-dot" style="width:.9rem;height:.9rem"></span>
                <h1 class="text-lg font-semibold text-slate-800">{{ $overall->headline() }}</h1>
            </div>
            <p class="mt-1 text-sm text-slate-500">Atualizado em {{ $lastUpdated }}</p>
        </section>

        {{-- Componentes --}}
        <section class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Componentes</h2>
                <span class="text-xs text-slate-400">Uptime · últimos 90 dias</span>
            </div>

            @foreach ($groups as $group)
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-2.5">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $group['name'] }}</h3>
                </div>

                @foreach ($group['components'] as $component)
                    <div class="border-b border-slate-100 px-6 py-4 last:border-0">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-slate-700">{{ $component['name'] }}</span>
                            <span class="st-badge st--{{ $component['status']->value }}">
                                <span class="st-dot"></span>{{ $component['status']->label() }}
                            </span>
                        </div>

                        <div class="mt-3 flex items-center gap-3">
                            <div class="uptime-bar">
                                @foreach ($component['history'] as $day)
                                    <i class="seg st--{{ $day['status']->value }}"
                                       title="{{ $day['date']->format('d/m') }} — {{ $day['status']->label() }}"></i>
                                @endforeach
                            </div>
                            <span class="shrink-0 text-xs font-semibold tabular-nums text-slate-500">
                                {{ number_format($component['uptime'], 2, ',', '.') }}%
                            </span>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </section>

        {{-- Histórico de incidentes --}}
        <section class="mb-8 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-6 py-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Histórico de incidentes</h2>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($incidents as $incident)
                    <article class="px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="font-medium text-slate-700">{{ $incident['title'] }}</h3>
                            <span class="st-badge st--{{ $incident['badge']->value }} shrink-0">
                                {{ $incident['badge_label'] }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $incident['detail'] }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ $incident['date'] }} · duração {{ $incident['duration'] }}</p>
                    </article>
                @empty
                    <p class="px-6 py-8 text-center text-sm text-slate-400">Nenhum incidente nos últimos 90 dias.</p>
                @endforelse
            </div>
        </section>

        {{-- Rodapé --}}
        <footer class="py-6 text-center text-xs text-slate-400">
            Blue3 — Provedor de Internet ·
            <a href="https://blue3.com.br" class="transition hover:text-slate-600">blue3.com.br</a>
        </footer>
    </div>
</body>
</html>
