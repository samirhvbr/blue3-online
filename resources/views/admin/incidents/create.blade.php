@extends('layouts.admin')

@section('title', 'Novo incidente')

@section('content')
    <h1 class="mb-6 text-xl font-semibold">Novo incidente</h1>

    <form method="POST" action="{{ route('admin.incidents.store') }}"
          class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        @include('admin.incidents._form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-lg bg-[#0b63ce] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a57b5]">Criar</button>
            <a href="{{ route('admin.incidents.index') }}" class="rounded-lg px-4 py-2 text-sm text-slate-500 hover:text-slate-700">Cancelar</a>
        </div>
    </form>
@endsection
