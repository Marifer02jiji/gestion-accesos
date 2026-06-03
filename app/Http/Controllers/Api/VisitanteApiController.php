<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ListaExclusionVisitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitanteApiController extends Controller
{
    public function __construct(
        private readonly ListaExclusionVisitaService $listaExclusion
    ) {
    }

    public function porCorreo(Request $request): JsonResponse
    {
        $request->validate([
            'correo' => ['required', 'email', 'max:150'],
        ]);

        return response()->json([
            'data' => $this->listaExclusion->datosPorCorreo($request->correo),
        ]);
    }

    public function porCorreoVigilante(Request $request): JsonResponse
    {
        $request->validate([
            'correo' => ['required', 'email', 'max:150'],
        ]);

        return response()->json([
            'data' => $this->listaExclusion->datosPorCorreo($request->correo),
        ]);
    }
}
