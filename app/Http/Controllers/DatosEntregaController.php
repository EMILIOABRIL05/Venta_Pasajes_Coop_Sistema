<?php

namespace App\Http\Controllers;

class DatosEntregaController extends Controller
{
    public function __invoke()
    {
        return view('admin.datos-entrega');
    }
}
