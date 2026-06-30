<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{

    public function showLogin()
    {
        return view('teacher.login');
    }


    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Email hoặc mật khẩu không đúng.',
                    'errors' => ['email' => ['Email hoặc mật khẩu không đúng.']],
                ], 422);
            }
            return back()
                ->withErrors(['email' => 'Email hoặc mật khẩu không đúng.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $intended = $request->session()->pull('url.intended', route('teacher.dashboard'));
        return $this->respondOk($request, 'Đăng nhập thành công', $intended);
    }

    public function showRegister()
    {
        return view('teacher.register');
    }


    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // tự hash nhờ cast 'hashed'
            'role'     => 'owner',
            'status'   => 'active',
        ]);

        $user->update(['tenant_id' => $user->id]);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->respondOk($request, 'Đăng ký thành công', route('teacher.dashboard'));
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->respondOk($request, 'Đã đăng xuất', route('teacher.login'));
    }
}
