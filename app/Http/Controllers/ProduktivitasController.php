<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\WorkSchedule;

class ProduktivitasController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.analisis', ['tab' => 'produktivitas']);
    }
}
