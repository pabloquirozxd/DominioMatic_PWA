<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Cambiar el rol de un usuario (Admin o Member).
     */
    public function updateRole(Request $request, User $user)
    {
        $auth = $request->user();

        // El admin solo puede modificar usuarios de su propia empresa
        if ($auth->role !== 'owner' && $user->company_id !== $auth->company_id) {
            abort(403, 'No puedes gestionar usuarios de otra empresa.');
        }

        $request->validate([
            'role' => ['required', Rule::in(['admin', 'member'])],
        ]);

        $user->update(['role' => $request->role]);

        return back()->with('success', "Rol de {$user->name} actualizado a '{$request->role}'.");
    }

    /**
     * Eliminar un miembro de la empresa.
     */
    public function destroy(Request $request, User $user)
    {
        $auth = $request->user();

        if ($auth->role !== 'owner' && $user->company_id !== $auth->company_id) {
            abort(403);
        }

        if ($user->id === $auth->id) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return back()->with('success', 'El usuario ha sido removido correctamente.');
    }

    /**
     * Invitar usuario / Crear nueva empresa para ellos.
     */
    public function invite(Request $request)
    {
        $auth = $request->user();
        $isOwner = $auth->role === 'owner';

        $request->validate([
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,member',
            'company_id' => $isOwner ? 'required' : 'nullable',
            'new_company_name' => 'required_if:company_id,new|string|max:255',
        ]);

        // Lógica de invitación aquí:
        // 1. Si es "new", crear la empresa
        // 2. Crear el usuario (o enviar correo de invitación con un token)
        // 3. Asignar el rol y la empresa.

        return back()->with('success', 'Invitación enviada correctamente a ' . $request->email);
    }
}