<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;

class UserReportModerationController extends Controller
{
    public function index()
    {
        $reports = UserReport::with('user')->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'admin_reply' => ['required', 'string', 'min:5'],
            'status' => ['required', 'string', 'in:processed,resolved']
        ]);

        $report = UserReport::findOrFail($id);
        $report->admin_reply = $request->input('admin_reply');
        $report->status = $request->input('status');
        $report->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Laporan kendala berhasil ditanggapi dan status diperbarui.',
            'data' => $report
        ]);
    }
}
