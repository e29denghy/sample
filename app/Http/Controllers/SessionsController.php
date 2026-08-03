<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SessionsController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('guest', only: ['create', 'store'])];
    }

    public function create(): View
    {
        return view('sessions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->with('danger', '很抱歉，您的邮箱和密码不匹配');
        }

        $request->session()->regenerate();

        if (! Auth::user()->activated) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/')->with('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
        }

        return redirect()->intended(route('users.show', Auth::user()))
            ->with('success', '欢迎回来！');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', '您已成功退出！');
    }
}
