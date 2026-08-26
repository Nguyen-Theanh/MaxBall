<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionAnnouncement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionAnnouncementController extends Controller
{
    public function index(): View
    {
        $promotionAnnouncements = PromotionAnnouncement::query()
            ->latest('id')
            ->paginate(10);

        return view('admin.promotion-announcements.index', compact('promotionAnnouncements'));
    }

    public function create(): View
    {
        return view('admin.promotion-announcements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        PromotionAnnouncement::create($this->validatedData($request));

        return redirect()
            ->route('admin.promotion-announcements.index')
            ->with('success', 'Đã thêm thông báo khuyến mãi.');
    }

    public function edit(PromotionAnnouncement $promotionAnnouncement): View
    {
        return view('admin.promotion-announcements.edit', compact('promotionAnnouncement'));
    }

    public function update(Request $request, PromotionAnnouncement $promotionAnnouncement): RedirectResponse
    {
        $promotionAnnouncement->update($this->validatedData($request));

        return redirect()
            ->route('admin.promotion-announcements.index')
            ->with('success', 'Đã cập nhật thông báo khuyến mãi.');
    }

    public function destroy(PromotionAnnouncement $promotionAnnouncement): RedirectResponse
    {
        $promotionAnnouncement->delete();

        return redirect()
            ->route('admin.promotion-announcements.index')
            ->with('success', 'Đã xóa thông báo khuyến mãi.');
    }

    public function toggleStatus(PromotionAnnouncement $promotionAnnouncement): RedirectResponse
    {
        $promotionAnnouncement->update([
            'is_active' => ! $promotionAnnouncement->is_active,
        ]);

        return back()->with('success', 'Đã cập nhật trạng thái thông báo.');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['required', 'string', 'max:3000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'title.max' => 'Tiêu đề không được vượt quá 120 ký tự.',
            'content.required' => 'Vui lòng nhập nội dung khuyến mãi.',
            'content.max' => 'Nội dung không được vượt quá 3.000 ký tự.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
