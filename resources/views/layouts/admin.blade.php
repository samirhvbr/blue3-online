<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Painel') — Blue3 Status</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f8fa] text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-baseline gap-2">
                    <span class="text-lg font-bold text-[#0b63ce]">Blue3</span>
                    <span class="text-lg font-light text-slate-400">Admin</span>
                </a>
                <nav class="flex gap-4 text-sm">
                    <a href="{{ route('admin.dashboard') }}" class="text-slate-600 hover:text-slate-900">Painel</a>
                    <a href="{{ route('admin.components.index') }}" class="text-slate-600 hover:text-slate-900">Componentes</a>
                    <a href="{{ route('admin.incidents.index') }}" class="text-slate-600 hover:text-slate-900">Incidentes</a>
                </nav>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('status.index') }}" target="_blank" class="text-slate-500 hover:text-slate-700">Ver site&nbsp;↗</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-slate-500 hover:text-red-600">Sair</button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
