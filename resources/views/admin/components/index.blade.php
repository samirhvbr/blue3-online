@extends('layouts.admin')

@section('title', 'Componentes')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Componentes</h1>
        <a href="{{ route('admin.components.create') }}"
           class="rounded-lg bg-[#0b63ce] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a57b5]">+ Novo</a>
    </div>

    @forelse ($groups as $group)
        <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 bg-slate-50 px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-400">
                {{ $group->name }}
            </div>
            @forelse ($group->components as $component)
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3 last:border-0">
                    <div class="flex items-center gap-3">
                        <span class="st-badge st--{{ $component->status->value }}"><span class="st-dot"></span>{{ $component->status->label() }}</span>
                        <span class="font-medium text-slate-700">{{ $component->name }}</span>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('admin.components.edit', $component) }}" class="text-[#0b63ce] hover:underline">Editar</a>
                        <form method="POST" action="{{ route('admin.components.destroy', $component) }}"
                              onsubmit="return confirm('Remover {{ $component->name }}?')">
                            @csrf @method('DELETE')
                            <button class="text-slate-400 hover:text-red-600">Remover</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="px-5 py-4 text-sm text-slate-400">Nenhum componente neste grupo.</p>
            @endforelse
        </div>
    @empty
        <p class="text-sm text-slate-400">Nenhum componente ainda. Crie o primeiro.</p>
    @endforelse
@endsection
