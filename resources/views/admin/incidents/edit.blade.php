@extends('layouts.admin')

@section('title', 'Editar incidente')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Editar incidente</h1>

    <form method="POST" action="{{ route('admin.incidents.update', $incident) }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @csrf @method('PUT')
        @include('admin.incidents._form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-lg bg-[#0b63ce] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a57b5]">Salvar</button>
            <a href="{{ route('admin.incidents.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Cancelar</a>
        </div>
    </form>
@endsection
