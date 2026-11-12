<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PizarronActivityController extends Controller
{
    /**
     * Return recent activity grouped by method and simple counts.
     */
    public function index(Request $request)
    {
        $minutes = (int) $request->query('minutes', 5);

        $since = now()->subMinutes($minutes);

        $query = ActivityLog::where('created_at', '>=', $since);

        $byMethod = $query->selectRaw('method, count(*) as total')
            ->groupBy('method')
            ->orderBy('method')
            ->get()
            ->mapWithKeys(fn($r) => [$r->method => $r->total]);

        $recent = ActivityLog::where('created_at', '>=', $since)
            ->latest()
            ->limit(50)
            ->get(['id','method','path','user_id','ip','created_at']);

        return response()->json([
            'minutes' => $minutes,
            'counts' => $byMethod,
            'recent' => $recent,
        ]);
    }
}
