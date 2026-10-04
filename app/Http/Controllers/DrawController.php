<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DrawController extends Controller
{
    public function index(ArisanRound $round)
    {
        $round->load(['group.members.user', 'host', 'winner', 'payments.user']);
        $group = $round->group;

        // Get members who haven't won yet in this group
        $eligibleMembersQuery = GroupMember::where('group_id', $group->id)
            ->where('is_active', true)
            ->where('has_won', false)
            ->with('user');

        $allNonWinners = $eligibleMembersQuery->get();

        // Check paid status for each non-winner in this round
        $paidUserIds = Payment::where('round_id', $round->id)
            ->where('payment_status', 'paid')
            ->pluck('user_id')
            ->toArray();

        $eligibleCandidates = $allNonWinners->map(function ($gm) use ($paidUserIds) {
            $isPaid = in_array($gm->user_id, $paidUserIds);
            return [
                'id' => $gm->user->id,
                'name' => $gm->user->name,
                'phone' => $gm->user->phone,
                'is_paid' => $isPaid,
                'order_no' => $gm->fixed_order_number,
            ];
        });

        // Filter based on group rule
        $canDrawCandidates = $group->only_paid_can_win
            ? $eligibleCandidates->where('is_paid', true)->values()
            : $eligibleCandidates->values();

        $allMembersCount = $group->members()->where('is_active', true)->count();
        $totalPrize = (float)$group->contribution_amount * $allMembersCount;

        // Previous winners in this group
        $pastWinners = ArisanRound::where('group_id', $group->id)
            ->where('status', 'completed')
            ->with('winner')
            ->orderBy('round_number')
            ->get();

        return view('draws.index', compact(
            'round',
            'group',
            'eligibleCandidates',
            'canDrawCandidates',
            'totalPrize',
            'pastWinners'
        ));
    }

    public function process(Request $request, ArisanRound $round)
    {
        if ($round->status === 'completed') {
            return back()->with('error', 'Putaran arisan ini sudah memiliki pemenang!');
        }

        $forcedWinnerId = $request->input('winner_id');

        try {
            $winner = ArisanService::executeDraw($round, $forcedWinnerId ? (int)$forcedWinnerId : null);

            return redirect()->route('draws.index', $round)
                ->with('winner_modal', true)
                ->with('success', "Selamat kepada Ibu {$winner->name}, terpilih sebagai pemenang arisan putaran ke-{$round->round_number}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function disburse(Request $request, ArisanRound $round)
    {
        $validated = $request->validate([
            'disbursement_proof' => 'nullable|image|max:3072',
            'disbursement_notes' => 'nullable|string|max:1000',
            'disbursed_at' => 'required|date',
        ]);

        $proofPath = $round->disbursement_proof;
        if ($request->hasFile('disbursement_proof')) {
            $proofPath = $request->file('disbursement_proof')->store('disbursement_proofs', 'public');
        }

        $round->update([
            'prize_disbursed' => true,
            'disbursed_at' => $validated['disbursed_at'],
            'disbursement_proof' => $proofPath,
            'disbursement_notes' => $validated['disbursement_notes'] ?? null,
        ]);

        // Send WhatsApp notification
        WhatsAppService::notifyDisbursement($round);

        ActivityLog::log(
            'pencairan_arisan',
            "Pencairan dana arisan putaran ke-{$round->round_number} kepada Ibu {$round->winner->name} (Rp " . number_format($round->winning_amount, 0, ',', '.') . ")"
        );

        return back()->with('success', "Alhamdulillah! Pencairan dana arisan kepada Ibu {$round->winner->name} telah berhasil dicatat & disimpan.");
    }
}
