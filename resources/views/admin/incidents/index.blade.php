@extends('layouts.admin')

@section('title', 'Incidentes')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Incidentes</h1>
        <a href="{{ route('admin.incidents.create') }}"
           class="rounded-lg bg-[#0b63ce] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a57b5]">+ Novo</a>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        @forelse ($incidents as $incident)
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="st-badge st--{{ $incident->impact->value }}">{{ $incident->impact->label() }}</span>
                        <span class="font-medium text-slate-700">{{ $incident->title }}</span>
                    </div>
                    <div class="mt-1 text-xs text-slate-400">
                        {{ $incident->component?->name ?? 'Global (todos)' }}
                        · {{ $incident->started_at->format('d/m/Y H:i') }}
                        · {{ $incident->isResolved() ? 'resolvido' : 'aberto' }}
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <a href="{{ route('admin.incidents.edit', $incident) }}" class="text-[#0b63ce] hover:underline">Editar</a>
                    <form method="POST" action="{{ route('admin.incidents.destroy', $incident) }}"
                          onsubmit="return confirm('Remover este incidente?')">
                        @csrf @method('DELETE')
                        <button class="text-slate-400 hover:text-red-600">Remover</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="px-5 py-6 text-center text-sm text-slate-400">Nenhum incidente registrado.</p>
        @endforelse
    </div>
@endsection
