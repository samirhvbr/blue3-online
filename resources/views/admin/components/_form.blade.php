<div class="space-y-4">
    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Nome</label>
        <input type="text" name="name" value="{{ old('name', $component->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#0b63ce] focus:outline-none">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Grupo</label>
        <select name="component_group_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach ($groups as $group)
                <option value="{{ $group->id }}" @selected(old('component_group_id', $component->component_group_id) == $group->id)>
                    {{ $group->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Status atual</label>
        <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected(old('status', $component->status?->value) === $status->value)>
                    {{ $status->label() }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-600">Posição (ordem)</label>
        <input type="number" name="position" value="{{ old('position', $component->position ?? 0) }}" min="0"
               class="w-32 rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>
</div>
