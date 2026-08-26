<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->status) {
            if ($request->user()->is($user)) {
                return back()->withErrors(['user' => 'Bạn không thể khóa chính tài khoản đang đăng nhập.']);
            }
            if ($user->role === 'admin' && $this->activeAdminCount() <= 1) {
                return back()->withErrors(['user' => 'Cần giữ lại ít nhất một admin đang hoạt động.']);
            }
            $user->update(['status' => false]);
            $message = 'Đã khóa tài khoản.';
        } else {
            $user->update(['status' => true]);
            $message = 'Đã mở khóa tài khoản.';
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $message);
    }

    public function toggleRole(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'Bạn không thể thay đổi quyền của chính tài khoản đang đăng nhập.']);
        }

        if (! in_array($user->role, self::ROLES, true)) {
            return back()->withErrors(['user' => 'Role hiện tại của tài khoản không hợp lệ.']);
        }

        $newRole = $user->role === 'admin' ? 'customer' : 'admin';

        if ($this->wouldRemoveLastActiveAdmin($user, [
            'role' => $newRole,
            'status' => $user->status,
        ])) {
            return back()->withErrors(['user' => 'Cần giữ lại ít nhất một admin đang hoạt động.']);
        }

        $user->update(['role' => $newRole]);

        return back()->with('success', sprintf(
            'Đã chuyển tài khoản %s thành %s.',
            $user->name,
            ucfirst($newRole),
        ));
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
