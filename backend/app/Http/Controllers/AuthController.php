<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Credenciais inválidas.'
            ], 401);
        }

        $request->session()->regenerate();

        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ]);
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => [
                'required',
                'string',
                'in:' . implode(',', array_map(
                    fn($role) => $role->value,
                    UserRole::cases()
                )),
            ],
        ]);

        $user = User::create($validatedData);

        Auth::login($user);

        $request->session()->regenerate();

        return response()->json([
            'user' => $user,
        ], 201);
    }
}
