<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComponentStatus;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\ComponentGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ComponentController extends Controller
{
    public function index(): View
    {
        return view('admin.components.index', [
            'groups' => ComponentGroup::with('components')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.components.create', [
            'component' => new Component(['status' => ComponentStatus::Operational]),
            'groups' => ComponentGroup::orderBy('position')->get(),
            'statuses' => ComponentStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Component::create($this->validated($request));

        return redirect()->route('admin.components.index')->with('status', 'Componente criado.');
    }

    public function edit(Component $component): View
    {
        return view('admin.components.edit', [
            'component' => $component,
            'groups' => ComponentGroup::orderBy('position')->get(),
            'statuses' => ComponentStatus::cases(),
        ]);
    }

    public function update(Request $request, Component $component): RedirectResponse
    {
        $component->update($this->validated($request));

        return redirect()->route('admin.components.index')->with('status', 'Componente atualizado.');
    }

    public function destroy(Component $component): RedirectResponse
    {
        $component->delete();

        return redirect()->route('admin.components.index')->with('status', 'Componente removido.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'component_group_id' => ['required', 'exists:component_groups,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', new Enum(ComponentStatus::class)],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['position'] = $data['position'] ?? 0;

        return $data;
    }
}
