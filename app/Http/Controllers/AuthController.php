<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $data['role'] = User::query()->exists() ? 'customer' : 'admin';
        $data['status'] = true;

        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('client.products.index')
            ->with('success', 'Dang ky thanh cong.');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email hoac mat khau khong dung.'])
                ->onlyInput('email');
        }

        if (! Auth::user()->status) {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Tai khoan cua ban dang bi khoa.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('client.products.index'))
            ->with('success', 'Dang nhap thanh cong.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('client.products.index')
            ->with('success', 'Da dang xuat.');
    }
}
