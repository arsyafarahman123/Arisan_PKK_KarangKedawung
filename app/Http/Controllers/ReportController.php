<?php

namespace App\Http\Controllers;

use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->get('group_id');
        $groups = ArisanGroup::with('rounds')->get();

        $selectedGroup = $groupId ? ArisanGroup::find($groupId) : $groups->first();

        $financialSummary = [];
        if ($selectedGroup) {
            $totalExpected = Payment::where('group_id', $selectedGroup->id)->sum('amount');
            $totalCollected = Payment::where('group_id', $selectedGroup->id)->where('payment_status', 'paid')->sum('amount');
            $totalPenalties = Payment::where('group_id', $selectedGroup->id)->where('payment_status', 'paid')->sum('penalty_amount');
            $totalPending = Payment::where('group_id', $selectedGroup->id)->whereIn('payment_status', ['unpaid', 'late'])->sum('amount');
            $totalDisbursed = ArisanRound::where('group_id', $selectedGroup->id)->where('prize_disbursed', true)->sum('winning_amount');

            $roundsData = ArisanRound::where('group_id', $selectedGroup->id)
                ->with(['host', 'winner', 'payments'])
                ->orderBy('round_number')
                ->get();

            $financialSummary = [
                'totalExpected' => $totalExpected,
                'totalCollected' => $totalCollected,
                'totalPenalties' => $totalPenalties,
                'totalPending' => $totalPending,
                'totalDisbursed' => $totalDisbursed,
                'rounds' => $roundsData,
            ];
        }

        // All active members and their win status
        $memberStatus = $selectedGroup ? GroupMember::where('group_id', $selectedGroup->id)
            ->with(['user', 'wonRound'])
            ->get() : collect();

        // Arrears list
        $arrears = Payment::whereIn('payment_status', ['unpaid', 'late'])
            ->whereHas('round', function ($q) {
                $q->where('status', '!=', 'pending');
            })
            ->when($selectedGroup, function ($q) use ($selectedGroup) {
                $q->where('group_id', $selectedGroup->id);
            })
            ->with(['user', 'group', 'round'])
            ->orderBy('due_date')
            ->get();

        return view('reports.index', compact(
            'groups',
            'selectedGroup',
            'financialSummary',
            'memberStatus',
            'arrears'
        ));
    }

    public function transparency(Request $request, ?ArisanGroup $group = null)
    {
        if (!$group) {
            $group = ArisanGroup::where('status', 'active')->first() ?? ArisanGroup::first();
        }

        if (!$group) {
            return redirect()->route('dashboard')->with('info', 'Belum ada data kelompok arisan.');
        }

        $group->load([
            'admin',
            'members.user',
            'members.wonRound',
            'rounds.host',
            'rounds.winner',
            'rounds.payments.user'
        ]);

        $rounds = $group->rounds;
        $members = $group->members;
        $totalCollected = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('amount');
        $totalPenalties = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('penalty_amount');
        $totalDisbursed = ArisanRound::where('group_id', $group->id)->where('prize_disbursed', true)->sum('winning_amount');
        $currentRound = $group->currentRound();

        $allGroups = ArisanGroup::all();

        return view('reports.transparency', compact(
            'group',
            'allGroups',
            'rounds',
            'members',
            'totalCollected',
            'totalPenalties',
            'totalDisbursed',
            'currentRound'
        ));
    }

    public function printReport(ArisanGroup $group)
    {
        $group->load([
            'admin',
            'members.user',
            'rounds.host',
            'rounds.winner',
            'rounds.payments.user'
        ]);

        $rounds = $group->rounds;
        $members = $group->members;

        $totalCollected = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('amount');
        $totalPenalties = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('penalty_amount');
        $totalPending = Payment::where('group_id', $group->id)->whereIn('payment_status', ['unpaid', 'late'])->sum('amount');
        $totalDisbursed = ArisanRound::where('group_id', $group->id)->where('prize_disbursed', true)->sum('winning_amount');

        return view('reports.print', compact(
            'group',
            'rounds',
            'members',
            'totalCollected',
            'totalPenalties',
            'totalPending',
            'totalDisbursed'
        ));
    }

    public function exportCsv(ArisanGroup $group): StreamedResponse
    {
        $fileName = 'Laporan_Kas_Arisan_' . $group->slug . '_' . date('Y-m-d') . '.csv';

        $payments = Payment::where('group_id', $group->id)
            ->with(['user', 'round'])
            ->orderBy('round_id')
            ->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($payments, $group) {
            $handle = fopen('php://output', 'w');
            // Add BOM for Excel Indonesian / UTF-8
            fputs($handle, "\xEF\xBB\xBF");

            // Title
            fputcsv($handle, ['LAPORAN KAS & IURAN ARISAN PKK KARANGKEDAWUNG']);
            fputcsv($handle, ['Kelompok', $group->name]);
            fputcsv($handle, ['Nominal Iuran', 'Rp ' . number_format($group->contribution_amount, 0, ',', '.')]);
            fputcsv($handle, ['Tanggal Cetak', Carbon::now()->translatedFormat('d F Y H:i')]);
            fputcsv($handle, []);

            // Columns
            fputcsv($handle, [
                'Putaran',
                'Nama Anggota',
                'No WhatsApp',
                'Jatuh Tempo',
                'Pokok Iuran (Rp)',
                'Denda (Rp)',
                'Total Tagihan (Rp)',
                'Status Bayar',
                'Metode',
                'Tanggal Bayar',
            ]);

            foreach ($payments as $p) {
                $statusMap = [
                    'paid' => 'LUNAS',
                    'pending_verification' => 'MENUNGGU VERIFIKASI',
                    'unpaid' => 'BELUM BAYAR',
                    'late' => 'TELAT / TUNGGAKAN',
                ];

                fputcsv($handle, [
                    'Putaran ' . $p->round->round_number,
                    $p->user->name,
                    "'" . $p->user->phone,
                    $p->due_date ? Carbon::parse($p->due_date)->format('d/m/Y') : '-',
                    $p->amount,
                    $p->penalty_amount,
                    $p->total_amount,
                    $statusMap[$p->payment_status] ?? $p->payment_status,
                    $p->payment_method ?? 'Transfer',
                    $p->paid_at ? Carbon::parse($p->paid_at)->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
