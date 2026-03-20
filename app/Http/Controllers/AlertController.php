<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $familyId = auth()->user()->family_id;
        app(AlertService::class)->refreshTimedAlertsForFamily($familyId);

        $alerts = Alert::where('family_id', $familyId)
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->when($request->severity, fn ($q, $v) => $q->where('severity', $v))
            ->when($request->status, function ($q, $status) {
                if ($status === 'unread') {
                    $q->unread();
                } elseif ($status === 'read') {
                    $q->where('is_read', true);
                } elseif ($status === 'dismissed') {
                    $q->where('is_dismissed', true);
                }
            })
            ->when($request->sort, function ($q) use ($request) {
                $q->orderBy($request->sort, $request->direction === 'asc' ? 'asc' : 'desc');
            }, fn ($q) => $q->orderByDesc('created_at'))
            ->paginate(15)
            ->withQueryString();

        $unreadCount = Alert::where('family_id', $familyId)->unread()->count();
        $summary = [
            'total' => Alert::where('family_id', $familyId)->active()->count(),
            'danger' => Alert::where('family_id', $familyId)->active()->where('severity', 'danger')->count(),
            'warning' => Alert::where('family_id', $familyId)->active()->where('severity', 'warning')->count(),
            'unread' => $unreadCount,
        ];
        $types = Alert::where('family_id', $familyId)
            ->active()
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('alerts.index', compact('alerts', 'unreadCount', 'summary', 'types'));
    }

    public function markAsRead(Alert $alert)
    {
        abort_if($alert->family_id !== auth()->user()->family_id, 403);

        $alert->markAsRead();

        return redirect()->back()
            ->with('success', __('Alert marked as read.'));
    }

    public function read(Alert $alert)
    {
        return $this->markAsRead($alert);
    }

    public function markAllAsRead()
    {
        $familyId = auth()->user()->family_id;

        Alert::where('family_id', $familyId)
            ->unread()
            ->update(['is_read' => true, 'read_at' => now()]);

        return redirect()->back()
            ->with('success', __('All alerts marked as read.'));
    }

    public function readAll()
    {
        return $this->markAllAsRead();
    }

    public function dismiss(Alert $alert)
    {
        abort_if($alert->family_id !== auth()->user()->family_id, 403);

        $alert->dismiss();

        return redirect()->back()
            ->with('success', __('Alert dismissed.'));
    }

    public function getUnreadCount(): JsonResponse
    {
        $familyId = auth()->user()->family_id;

        $count = Alert::where('family_id', $familyId)->unread()->count();

        return response()->json(['count' => $count]);
    }

    public function unreadCount(): JsonResponse
    {
        return $this->getUnreadCount();
    }
}
