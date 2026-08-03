<?php

namespace App\Http\Controllers;

use App\Mail\RegistrationConfirmation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UsersController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['show', 'create', 'store', 'index', 'confirmEmail']),
            new Middleware('guest', only: ['create', 'store']),
        ];
    }

    public function index(): View
    {
        return view('users.index', ['users' => User::paginate(10)]);
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function show(User $user): View
    {
        return view('users.show', compact('user'));
    }

    public function confirmEmail(string $token): RedirectResponse
    {
        $user = User::where('activation_token', $token)->firstOrFail();
        $user->forceFill([
            'activated' => true,
            'activation_token' => null,
        ])->save();

        Auth::login($user);

        return redirect()->route('users.show', $user)->with('success', '恭喜你，激活成功！');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        $user = User::create($data);
        $this->sendEmailConfirmationTo($user);

        return redirect('/')->with('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
    }

    protected function sendEmailConfirmationTo(User $user): void
    {
        Mail::to($user->email)->send(new RegistrationConfirmation($user));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'confirmed', 'min:6'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.show', $user)->with('success', '个人资料更新成功！');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('destroy', $user);
        $user->delete();

        return back()->with('success', '成功删除用户！');
    }
}
