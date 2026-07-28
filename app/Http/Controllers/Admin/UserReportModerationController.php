<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserReport;

/**
 * Class UserReportModerationController
 *
 * Handles user report moderation controller operations for the application.
 */
class UserReportModerationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = UserReport::with('user')->latest()->get();
        return response()->json([
            'status' => 'success',
            'data' => $reports
        ]);
    }

    /**
     * Reply.
     */
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
