<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMessage;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        return view('client.pages.contact');
    }

    public function submit(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->email || !$user->phone) {
            return redirect()->back()->withInput()->with('contact_error', 'Bạn cần cập nhật đủ Email và Số điện thoại trong tài khoản để gửi liên hệ.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:2000',
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'message.required' => 'Vui lòng nhập nội dung liên hệ.',
        ]);

        try {
            // Lưu vào database
            Contact::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'message' => $validated['message'],
                'status' => 'pending',
            ]);

            // Gửi email tới địa chỉ tuankaka554@gmail.com
            Mail::to('tuankaka554@gmail.com')->send(new ContactMessage($validated));
            
            return redirect()->back()->with('contact_success', 'Cảm ơn bạn! Tin nhắn của bạn đã được gửi thành công. Chúng tôi sẽ liên hệ lại sớm nhất.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('contact_error', 'Có lỗi xảy ra khi gửi email, vui lòng thử lại sau. Lỗi: ' . $e->getMessage());
        }
    }
}
