<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComponentStatus;
use App\Http\Controllers\Controller;
use App\Models\Component;
use App\Models\Incident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class IncidentController extends Controller
{
    public function index(): View
    {
        return view('admin.incidents.index', [
            'incidents' => Incident::with('component')->orderByDesc('started_at')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.incidents.create', [
            'incident' => new Incident(['impact' => ComponentStatus::Degraded, 'started_at' => now()]),
            'components' => Component::orderBy('name')->get(),
            'impacts' => $this->impacts(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Incident::create($this->validated($request));

        return redirect()->route('admin.incidents.index')->with('status', 'Incidente criado.');
    }

    public function edit(Incident $incident): View
    {
        return view('admin.incidents.edit', [
            'incident' => $incident,
            'components' => Component::orderBy('name')->get(),
            'impacts' => $this->impacts(),
        ]);
    }

    public function update(Request $request, Incident $incident): RedirectResponse
    {
        $incident->update($this->validated($request));

        return redirect()->route('admin.incidents.index')->with('status', 'Incidente atualizado.');
    }

    public function destroy(Incident $incident): RedirectResponse
    {
        $incident->delete();

        return redirect()->route('admin.incidents.index')->with('status', 'Incidente removido.');
    }

    /** Impactos válidos para um incidente (exclui "operacional"). */
    private function impacts(): array
    {
        return array_values(array_filter(
            ComponentStatus::cases(),
            fn (ComponentStatus $s) => $s !== ComponentStatus::Operational,
        ));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'detail' => ['nullable', 'string'],
            'impact' => ['required', new Enum(ComponentStatus::class)],
            'component_id' => ['nullable', 'exists:components,id'],
            'started_at' => ['required', 'date'],
            'resolved_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ]);
    }
}
