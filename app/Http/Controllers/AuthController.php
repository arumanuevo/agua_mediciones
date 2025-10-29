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
        // Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
    
        // Buscar al usuario por email
        $user = User::where('email', $request->email)->first();
    
        // Si el usuario no existe o la contraseña es incorrecta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Las credenciales son incorrectas.',
                'errors' => ['email' => ['Las credenciales proporcionadas no coinciden con nuestros registros.']],
            ], 401); // Código 401: No autorizado
        }
    
        // Crear token de Sanctum
        $token = $user->createToken('api-token')->plainTextToken;
    
        // Respuesta exitosa
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

    /**
     * Obtener los datos del usuario autenticado.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'lotes' => $user->lotes(), // Método que ya tienes en el modelo User
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    /**
     * Obtener los datos de un usuario específico (solo administradores).
     */
    public function show(User $user)
    {
        $this->authorize('gestionar-usuarios'); // Usa el gate que definimos en AuthServiceProvider

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'lotes' => $user->lotes(),
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

}

