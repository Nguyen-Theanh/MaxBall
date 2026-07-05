<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLES = ['customer', 'admin'];

    public function index(Request $request): View
    {
        $users = User::query()
            ->withCount('orders')
            ->when($request->filled('q'), function ($query) use ($request) {
                $keyword = trim($request->input('q'));

                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->integer('status')))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['role' => 'customer', 'status' => true]),
            'roles' => self::ROLES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request, null, true);
        $data['status'] = $request->boolean('status');

        User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã tạo tài khỏan mới.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validatedData($request, $user);
        $data['status'] = $request->boolean('status');

        if ($this->wouldRemoveLastActiveAdmin($user, $data)) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'Cần giữ lại ít nhất một admin đang họat động.']);
        }

        if ($request->user()->is($user) && ($data['role'] !== 'admin' || ! $data['status'])) {
            return back()
                ->withInput()
                ->withErrors(['role' => 'Bạn không thể tự hạ quyền hoặc khóa chính tài khỏan đang đăng nhập.']);
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã cập nhật tài khoản.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Bạn không thể xóa chính tài khoản đang đăng nhập.']);
        }

        if ($user->role === 'admin' && $user->status && $this->activeAdminCount() <= 1) {
            return back()->withErrors(['user' => 'Cần giữ lại ít nhất một admin đang hoạt động.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Đã xóa tài khoản.');
    }

    private function validatedData(Request $request, ?User $user = null, bool $creating = false): array
    {
        $passwordRules = $creating
            ? ['required', 'confirmed', Password::min(8)]
            : ['nullable', 'confirmed', Password::min(8)];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'regex:/^0[0-9]{9}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => $passwordRules,
        ], [
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng số 0.',
        ]);
    }

    private function wouldRemoveLastActiveAdmin(User $user, array $data): bool
    {
        if ($user->role !== 'admin' || ! $user->status) {
            return false;
        }

        return ($data['role'] !== 'admin' || ! $data['status']) && $this->activeAdminCount() <= 1;
    }

    private function activeAdminCount(): int
    {
        return User::where('role', 'admin')->where('status', true)->count();
    }
}
