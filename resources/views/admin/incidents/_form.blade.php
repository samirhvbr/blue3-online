<div class="space-y-4">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Título</label>
        <input type="text" name="title" value="{{ old('title', $incident->title) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b63ce] focus:outline-none">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Descrição</label>
        <textarea name="detail" rows="3"
                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b63ce] focus:outline-none">{{ old('detail', $incident->detail) }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Impacto</label>
            <select name="impact" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach ($impacts as $impact)
                    <option value="{{ $impact->value }}" @selected(old('impact', $incident->impact?->value) === $impact->value)>
                        {{ $impact->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Componente afetado</label>
            <select name="component_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Global (todos)</option>
                @foreach ($components as $component)
                    <option value="{{ $component->id }}" @selected(old('component_id', $incident->component_id) == $component->id)>
                        {{ $component->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Início</label>
            <input type="datetime-local" name="started_at" required
                   value="{{ old('started_at', optional($incident->started_at)->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-600">Resolvido em <span class="text-slate-400">(vazio = aberto)</span></label>
            <input type="datetime-local" name="resolved_at"
                   value="{{ old('resolved_at', optional($incident->resolved_at)->format('Y-m-d\TH:i')) }}"
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
    </div>
</div>
