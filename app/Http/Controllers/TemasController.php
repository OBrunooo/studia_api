<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Temas\TemasService;

class TemasController extends Controller
{
    protected $temasService;

    public function __construct (TemasService $temasService) {
        $this->temasService = $temasService;
    }

    public function temas() {
        return $this->temasService->listarTemas();
    }

    public function questoes(Request $request) {
        $id = $request->query("id");
        return $this->temasService->questoesTema($id);
    }
}
