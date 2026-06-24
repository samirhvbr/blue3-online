<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Blue3 Status</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f6f8fa] text-slate-800 antialiased">
    <div class="mx-auto flex min-h-screen max-w-sm flex-col justify-center px-4">
        <div class="mb-6 text-center">
            <span class="text-2xl font-bold text-[#0b63ce]">Blue3</span>
            <span class="text-2xl font-light text-slate-400">Status</span>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="mb-4 text-lg font-semibold">Acesso ao painel</h1>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b63ce] focus:outline-none">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-600">Senha</label>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b63ce] focus:outline-none">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-500">
                    <input type="checkbox" name="remember" class="rounded border-slate-300"> Lembrar neste navegador
                </label>
                <button type="submit"
                        class="w-full rounded-lg bg-[#0b63ce] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0a57b5]">
                    Entrar
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-slate-400">
            <a href="{{ route('status.index') }}" class="hover:text-slate-600">← Voltar ao site</a>
        </p>
    </div>
</body>
</html>
