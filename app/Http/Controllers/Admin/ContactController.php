<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = Contact::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        return view('admin.contacts.show', compact('contact'));
    }

    public function updateStatus(Request $request, Contact $contact)
    {
        $contact->update(['status' => $contact->status === 'pending' ? 'resolved' : 'pending']);
        return redirect()->back()->with('success', 'Đã cập nhật trạng thái liên hệ.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Đã xóa liên hệ thành công.');
    }
}
