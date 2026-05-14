<?php

namespace App\Http\Controllers\Exemplos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrimeVueController extends Controller
{
    public function index()
    {
        return Inertia::render('Exemplos/PrimeVue');
    }
}
