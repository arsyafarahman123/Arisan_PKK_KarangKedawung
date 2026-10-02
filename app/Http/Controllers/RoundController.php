<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\User;
use App\Services\ArisanService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function show(ArisanRound $round)
    {
        $round->load([
            'group.members.user',
            'host',
            'winner',
            'payments.user',
            'payments.verifier',
            'notificationLogs.user'
        ]);

        $group = $round->group;
        $allMembers = $group->members()->where('is_active', true)->with('user')->get();

        // Financial stats for this round
        $totalPaid = $round->payments->where('payment_status', 'paid')->sum('total_amount');
        $totalUnpaid = $round->payments->whereIn('payment_status', ['unpaid', 'late'])->sum('total_amount');
        $pendingCount = $round->payments->where('payment_status', 'pending_verification')->count();

        return view('rounds.show', compact(
            'round',
            'group',
            'allMembers',
            'totalPaid',
            'totalUnpaid',
            'pendingCount'
        ));
    }

    public function reschedule(Request $request, ArisanRound $round)
    {
        $validated = $request->validate([
            'due_date' => 'required|date',
            'draw_date' => 'required|date|after_or_equal:due_date',
            'cascade_next' => 'nullable|boolean',
        ]);

        try {
            ArisanService::rescheduleRound(
                $round,
                $validated['due_date'],
                $validated['draw_date'],
                $request->boolean('cascade_next')
            );

            return back()->with('success', "Jadwal putaran ke-{$round->round_number} berhasil diperbarui!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateHost(Request $request, ArisanRound $round)
    {
        $validated = $request->validate([
            'host_user_id' => 'nullable|exists:users,id',
            'host_location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $round->update($validated);

        ActivityLog::log(
            'update_tuan_rumah',
            "Mengubah info tuan rumah putaran ke-{$round->round_number} kelompok {$round->group->name}"
        );

        return back()->with('success', 'Informasi tuan rumah & tempat pertemuan arisan berhasil disimpan.');
    }

    public function calendar(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $groupId = $request->get('group_id');

        $currentMonth = Carbon::parse($month . '-01');
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $groups = ArisanGroup::where('status', '!=', 'completed')->get();

        $query = ArisanRound::with(['group', 'host', 'winner'])
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
                  ->orWhereBetween('draw_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            });

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        $rounds = $query->get();

        // Format calendar events
        $events = [];
        foreach ($rounds as $r) {
            // Due date event
            $events[] = [
                'type' => 'due',
                'date' => $r->due_date->format('Y-m-d'),
                'title' => 'Jatuh Tempo: ' . $r->group->name . ' (Putaran ' . $r->round_number . ')',
                'round' => $r,
                'color' => 'amber',
            ];

            // Draw date event
            $events[] = [
                'type' => 'draw',
                'date' => $r->draw_date->format('Y-m-d'),
                'title' => 'Pengocokan & Pertemuan: ' . $r->group->name . ' (Putaran ' . $r->round_number . ')',
                'host' => $r->host ? $r->host->name : 'Belum Ditentukan',
                'location' => $r->host_location,
                'round' => $r,
                'color' => 'emerald',
            ];
        }

        return view('rounds.calendar', compact('currentMonth', 'groups', 'groupId', 'events', 'rounds'));
    }
}
