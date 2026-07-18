<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatBlockedKeyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatKeywordController extends Controller
{
    /**
     * Display all blocked keywords.
     */
    public function index()
    {
        $keywords = ChatBlockedKeyword::orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pages.admin.chat-keywords.index', compact('keywords'));
    }

    /**
     * Store a new blocked keyword.
     */
    public function store(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:255|unique:chat_blocked_keywords,keyword',
        ], [
            'keyword.unique' => 'Kata ini sudah ada dalam daftar.',
            'keyword.required' => 'Kata terlarang tidak boleh kosong.',
        ]);

        ChatBlockedKeyword::create([
            'keyword'    => mb_strtolower(trim($request->keyword)),
            'is_active'  => true,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Kata terlarang berhasil ditambahkan.');
    }

    /**
     * Toggle active/inactive status of a keyword.
     */
    public function toggle(ChatBlockedKeyword $keyword)
    {
        $keyword->update(['is_active' => !$keyword->is_active]);
        $status = $keyword->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Kata \"{$keyword->keyword}\" berhasil {$status}.");
    }

    /**
     * Delete a blocked keyword.
     */
    public function destroy(ChatBlockedKeyword $keyword)
    {
        $word = $keyword->keyword;
        $keyword->delete();

        return back()->with('success', "Kata \"{$word}\" berhasil dihapus.");
    }
}
