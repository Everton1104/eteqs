<?php

namespace App\Http\Controllers;

class PainelController extends Controller
{
    /**
     * Painel do administrador (professor).
     */
    public function index()
    {
        return view('painel');
    }
}
