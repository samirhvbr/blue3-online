@extends('layouts.admin')

@section('title', 'Painel')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Painel</h1>

    <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="text-3xl font-bold text-slate-800">{{ $componentsCount }}</div>
            <div class="text-sm text-slate-500">Componentes</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <div class="text-3xl font-bold text-slate-800">{{ $openIncidents }}</div>
            <div class="text-sm text-slate-500">Incidentes abertos</div>
        </div>
        <a href="{{ route('admin.incidents.create') }}"
           class="flex items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-5 text-sm font-medium text-[#0b63ce] hover:bg-slate-50">
            + Novo incidente
        </a>
    </div>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Incidentes recentes</h2>
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        @forelse ($recentIncidents as $incident)
            <a href="{{ route('admin.incidents.edit', $incident) }}"
               class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0 hover:bg-slate-50">
                <span class="text-slate-700">{{ $incident->title }}</span>
                <span class="st-badge st--{{ $incident->impact->value }}">
                    {{ $incident->isResolved() ? 'Resolvido' : 'Aberto' }}
                </span>
            </a>
        @empty
            <p class="px-5 py-6 text-center text-sm text-slate-400">Nenhum incidente registrado.</p>
        @endforelse
    </div>
@endsection
