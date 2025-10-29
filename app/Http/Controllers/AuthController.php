<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string|in:administrador,inspector',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol usando Spatie
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
        ], 201);
    }

    /**
     * Iniciar sesión y devolver un token de Sanctum.
     */
    public function login(Request $request)
    {
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        return response()->json([
            'message' => 'Login exitoso',
            
            
        ]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // Crear token de Sanctum
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token,
            'roles' => $user->getRoleNames(),
        ]);
    }

    /**
     * Cerrar sesión (eliminar el token actual).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }
}

