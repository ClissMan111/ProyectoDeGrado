<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt(['correo' => $data['email'], 'password' => $data['password'], 'estado' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos, o la cuenta está desactivada.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->panel()));
    }

    public function register(Request $request)
    {
        $data = $request->validate(['nombres' => 'required|string|max:100', 'apellidos' => 'required|string|max:120', 'ci' => 'required|string|max:20|unique:pacientes,ci', 'telefono' => 'nullable|string|max:20', 'fecha_nacimiento' => 'nullable|date|before_or_equal:today', 'email' => 'required|email|max:150|unique:usuarios,correo', 'password' => ['required', 'confirmed', Password::min(8)], 'consentimiento' => 'accepted']);
        validator(['nombre' => $data['nombres'].' '.$data['apellidos']], ['nombre' => 'max:120'])->validate();
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['nombres'].' '.$data['apellidos'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'rol' => 'paciente', 'estado' => true]);
            Paciente::create(['usuario_id' => $user->id, ...collect($data)->only(['nombres', 'apellidos', 'ci', 'telefono', 'fecha_nacimiento'])->all()]);
        });

        return redirect()->route('login')->with('success', 'Tu cuenta está lista. Inicia sesión para reservar tu cita.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function profile(Request $request)
    {
        return view('account', ['user' => $request->user()]);
    }

    public function password(Request $request)
    {
        $data = $request->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', Password::min(8)]]);
        $request->user()->update(['password' => Hash::make($data['password'])]);
        $request->session()->regenerate();

        return back()->with('success', 'Contraseña actualizada.');
    }
}
