<?php

namespace App\Http\Controllers;

use App\Services\StatusService;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function __construct(private readonly StatusService $status)
    {
    }

    /** Página pública de status (blue3.online). */
    public function index(): View
    {
        return view('status.index', [
            'overall' => $this->status->overall(),
            'groups' => $this->status->groups(),
            'incidents' => $this->status->incidents(),
            'lastUpdated' => $this->status->lastUpdatedLabel(),
        ]);
    }
}
