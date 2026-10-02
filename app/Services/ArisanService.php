<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArisanService
{
    /**
     * Generate all rounds for an arisan group based on its active members.
     */
    public static function generateRounds(ArisanGroup $group, array $hostUserIds = []): int
    {
        return DB::transaction(function () use ($group, $hostUserIds) {
            $activeMembers = $group->activeMembers()->with('user')->get();
            $totalRounds = $activeMembers->count();

            if ($totalRounds < 2) {
                throw new \Exception('Minimal harus ada 2 anggota aktif untuk membuat jadwal arisan.');
            }

            // Remove existing pending/draft rounds if any
            $group->rounds()->delete();

            $startDate = Carbon::parse($group->start_date);

            $roundsCreated = 0;
            for ($i = 1; $i <= $totalRounds; $i++) {
                // Calculate due date & draw date
                $dueDate = match ($group->period_type) {
                    'weekly' => $startDate->copy()->addWeeks($i - 1),
                    'biweekly' => $startDate->copy()->addWeeks(($i - 1) * 2),
                    default => $startDate->copy()->addMonthsNoOverflow($i - 1), // monthly
                };

                // Draw date is default 3 days after due date or on due date
                $drawDate = $dueDate->copy()->addDays(2);

                $hostId = $hostUserIds[$i - 1] ?? $activeMembers->get(($i - 1) % $totalRounds)->user_id;

                $round = ArisanRound::create([
                    'group_id' => $group->id,
                    'round_number' => $i,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'draw_date' => $drawDate->format('Y-m-d'),
                    'host_user_id' => $hostId,
                    'host_location' => 'Rumah Tuan Rumah / Balai PKK RW',
                    'winning_amount' => (float)$group->contribution_amount * $totalRounds,
                    'status' => $i === 1 ? 'ongoing' : 'pending',
                ]);

                // Create initial payment bills for all active members in this round
                foreach ($activeMembers as $member) {
                    Payment::create([
                        'round_id' => $round->id,
                        'group_id' => $group->id,
                        'user_id' => $member->user_id,
                        'amount' => $group->contribution_amount,
                        'penalty_amount' => 0,
                        'total_amount' => $group->contribution_amount,
                        'due_date' => $round->due_date,
                        'payment_status' => 'unpaid',
                        'payment_method' => 'transfer',
                    ]);
                }

                $roundsCreated++;
            }

            $group->update(['status' => 'active']);

            ActivityLog::log(
                'generate_jadwal',
                "Membuat {$roundsCreated} jadwal putaran arisan untuk kelompok {$group->name}"
            );

            return $roundsCreated;
        });
    }

    /**
     * Reschedule a round and optionally cascade the date shifts to subsequent rounds.
     */
    public static function rescheduleRound(
        ArisanRound $round,
        string $newDueDate,
        string $newDrawDate,
        bool $cascadeNext = true
    ): void {
        DB::transaction(function () use ($round, $newDueDate, $newDrawDate, $cascadeNext) {
            $oldDue = Carbon::parse($round->due_date);
            $newDue = Carbon::parse($newDueDate);
            $dayDifference = $oldDue->diffInDays($newDue, false);

            $round->update([
                'due_date' => $newDueDate,
                'draw_date' => $newDrawDate,
            ]);

            // Update due date for all unpaid bills in this round
            Payment::where('round_id', $round->id)
                ->where('payment_status', '!=', 'paid')
                ->update(['due_date' => $newDueDate]);

            // Cascade to subsequent rounds if requested and there is a time shift
            if ($cascadeNext && $dayDifference != 0) {
                $subsequentRounds = ArisanRound::where('group_id', $round->group_id)
                    ->where('round_number', '>', $round->round_number)
                    ->where('status', '!=', 'completed')
                    ->orderBy('round_number')
                    ->get();

                foreach ($subsequentRounds as $subRound) {
                    $updatedDue = Carbon::parse($subRound->due_date)->addDays($dayDifference)->format('Y-m-d');
                    $updatedDraw = Carbon::parse($subRound->draw_date)->addDays($dayDifference)->format('Y-m-d');

                    $subRound->update([
                        'due_date' => $updatedDue,
                        'draw_date' => $updatedDraw,
                    ]);

                    Payment::where('round_id', $subRound->id)
                        ->where('payment_status', '!=', 'paid')
                        ->update(['due_date' => $updatedDue]);
                }
            }

            ActivityLog::log(
                'reschedule_putaran',
                "Mengubah jadwal putaran ke-{$round->round_number} kelompok {$round->group->name} menjadi {$newDueDate}"
            );
        });
    }

    /**
     * Perform the draw for a round.
     */
    public static function executeDraw(ArisanRound $round, ?int $forcedWinnerUserId = null): User
    {
        return DB::transaction(function () use ($round, $forcedWinnerUserId) {
            $group = $round->group;

            // Find eligible members (active, in this group, haven't won yet)
            $eligibleQuery = GroupMember::where('group_id', $group->id)
                ->where('is_active', true)
                ->where('has_won', false)
                ->with('user');

            if ($group->only_paid_can_win) {
                // Member must have paid for current round
                $paidUserIds = Payment::where('round_id', $round->id)
                    ->where('payment_status', 'paid')
                    ->pluck('user_id');

                $eligibleQuery->whereIn('user_id', $paidUserIds);
            }

            $eligibleMembers = $eligibleQuery->get();

            if ($eligibleMembers->isEmpty()) {
                // If only_paid_can_win prevented, check if we have any non-winning members
                $allRemaining = GroupMember::where('group_id', $group->id)
                    ->where('is_active', true)
                    ->where('has_won', false)
                    ->get();

                if ($allRemaining->isEmpty()) {
                    throw new \Exception('Semua anggota sudah pernah menang!');
                }

                throw new \Exception('Tidak ada anggota yang memenuhi syarat (belum ada anggota yang lunas iuran pada putaran ini).');
            }

            $winnerMember = null;
            if ($forcedWinnerUserId) {
                $winnerMember = $eligibleMembers->firstWhere('user_id', $forcedWinnerUserId);
                if (!$winnerMember) {
                    throw new \Exception('Pemenang yang dipilih tidak memenuhi syarat atau sudah pernah menang.');
                }
            } else {
                // If mode is fixed_order
                if ($group->winner_determination === 'fixed_order') {
                    $winnerMember = $eligibleMembers->sortBy('fixed_order_number')->first();
                } else {
                    // Lottery mode: pick random
                    $winnerMember = $eligibleMembers->random();
                }
            }

            $winnerUser = $winnerMember->user;
            $totalPot = (float)$group->contribution_amount * $group->activeMembers()->count();

            // Mark round as completed
            $round->update([
                'winner_user_id' => $winnerUser->id,
                'winning_amount' => $totalPot,
                'status' => 'completed',
            ]);

            // Mark member as won
            $winnerMember->update([
                'has_won' => true,
                'won_round_id' => $round->id,
            ]);

            // Activate next round if exists
            $nextRound = ArisanRound::where('group_id', $group->id)
                ->where('round_number', $round->round_number + 1)
                ->first();

            if ($nextRound) {
                $nextRound->update(['status' => 'ongoing']);
            } else {
                // All rounds completed!
                $group->update(['status' => 'completed']);
            }

            // Create WhatsApp notification log
            WhatsAppService::notifyWinnerAnnouncement($round);

            ActivityLog::log(
                'kocok_arisan',
                "Pengocokan putaran ke-{$round->round_number} selesai. Pemenang: {$winnerUser->name} (Rp " . number_format($totalPot, 0, ',', '.') . ")"
            );

            return $winnerUser;
        });
    }

    /**
     * Check and apply penalties to all overdue payments
     */
    public static function checkAllOverduePenalties(): int
    {
        $unpaidPayments = Payment::whereIn('payment_status', ['unpaid', 'late'])
            ->with('group')
            ->get();

        $updatedCount = 0;
        foreach ($unpaidPayments as $payment) {
            $oldPenalty = $payment->penalty_amount;
            $payment->calculatePenalty();
            if ($payment->penalty_amount > $oldPenalty) {
                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
