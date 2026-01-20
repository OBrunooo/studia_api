<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\User\UserService as UserUserService;
use Illuminate\Support\Facades\Validator;
use App\Services\UserService;

class UserController extends Controller
{

    protected $userService;

    public function __construct(UserUserService $userService) {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function atualizaSenha(Request $request) {
        $validator = Validator::make($request->all(), [
            'senhaAntiga' => 'required|min:8',
            'senhaNova' => 'required|min:8'
        ], [
            'senhaAntiga.required' => 'Senha é obrigatória',
            'senhaAntiga.min'  => 'Senha precisa conter no mínimo 8 caracteres',
            'senhaNova.required' => 'Senha é obrigatória',
            'senhaNova.min'  => 'Senha precisa conter no mínimo 8 caracteres'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'mensagem' => $validator->errors()
            ], 422);
        }
        
        $senhas = [
            "senhaAntiga" => $request->input("senhaAntiga"),
            "senhaNova" => $request->input("senhaNova")
        ];

        return json_encode($this->userService->atualizaSenha($senhas));
    }

    public function atualizaAvatar(Request $request) {
        $validator = Validator::make($request->all(), [
            'avatarId' => 'required'
        ], [
            'avatarId.required' => 'Id do avatar é obrigatório'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'mensagem' => $validator->errors()
            ], 422);
        }        
        $avatarId = $request->input("avatarId");
        return json_encode($this->userService->atualizaAvatar($avatarId));

    }
}
