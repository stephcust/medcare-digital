<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class InicioController extends Controller
{
    public function inicioAutenticado()
    {
        // dd(Session::all());
        return Inertia::render("InicioAutenticado");
    }
}
