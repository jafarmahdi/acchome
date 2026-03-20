<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;

        $dateFrom = $request->input('date_from', $request->input('from'));
        $dateTo = $request->input('date_to', $request->input('to'));

        $logs = AuditLog::where('family_id', $familyId)
            ->with('user')
            ->when($request->user_id, fn ($q, $v) => $q->where('user_id', $v))
            ->when($request->action, fn ($q, $v) => $q->where('action', $v))
            ->when($request->model_type, fn ($q, $v) => $q->where('model_type', $v))
            ->when($dateFrom, fn ($q, $v) => $q->where('created_at', '>=', $v))
            ->when($dateTo, fn ($q, $v) => $q->where('created_at', '<=', $v . ' 23:59:59'))
            ->when($request->search, fn ($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $members = User::where('family_id', $familyId)->orderBy('name')->get();

        $actions = AuditLog::where('family_id', $familyId)
            ->distinct()
            ->pluck('action');

        $modelTypes = AuditLog::where('family_id', $familyId)
            ->distinct()
            ->whereNotNull('model_type')
            ->pluck('model_type')
            ->map(fn ($type) => class_basename($type));

        return view('audit.index', compact('logs', 'members', 'actions', 'modelTypes'));
    }
}
