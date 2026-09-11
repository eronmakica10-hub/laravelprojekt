<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UserStore;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('user_id')) return redirect('/');
        return view('auth.login');
    }

    public function showRegister()
    {
        if (session('user_id')) return redirect('/');
        return view('auth.register');
    }

    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|max:100',
            'password' => 'required|min:6|confirmed',
        ], [
            'name.required' => 'Shkruaj emrin.',
            'name.min' => 'Emri duhet të ketë së paku 3 shkronja.',
            'email.required' => 'Shkruaj email-in.',
            'email.email' => 'Email i pavlefshëm.',
            'password.required' => 'Shkruaj fjalëkalimin.',
            'password.min' => 'Fjalëkalimi duhet të ketë së paku 6 karaktere.',
            'password.confirmed' => 'Fjalëkalimet nuk përputhen.',
        ]);

        if (UserStore::findByEmail($data['email'])) {
            return back()->withErrors(['email' => 'Ky email është i regjistruar. Provo login.'])->withInput();
        }

        $user = UserStore::create($data['name'], $data['email'], $data['password']);
        session(['user_id' => $user['id'], 'user_name' => $user['name'], 'balance' => $user['balance']]);
        return redirect('/')->with('success', 'Mirësevjen ' . $user['name'] . '! Depozito nga 💳 Portofoli për të luajtur 🎰');
    }

    public function login(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Shkruaj email-in.',
            'password.required' => 'Shkruaj fjalëkalimin.',
        ]);

        $user = UserStore::findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password'])) {
            return back()->withErrors(['email' => 'Email ose fjalëkalim i gabuar.'])->withInput();
        }

        session(['user_id' => $user['id'], 'user_name' => $user['name'], 'balance' => $user['balance']]);
        return redirect('/')->with('success', 'Mirësev erdhe përsëri, ' . $user['name'] . '! 🎉');
    }

    public function logout()
    {
        session()->forget(['user_id', 'user_name', 'bj', 'tickets']);
        session(['balance' => 0]);
        return redirect('/')->with('success', 'Dole me sukses. Mirupafshim! 👋');
    }
}
