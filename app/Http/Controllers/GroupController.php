<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $query = ArisanGroup::with(['admin', 'members', 'rounds'])
            ->withCount('members');

        if ($status && in_array($status, ['draft', 'active', 'completed'])) {
            $query->where('status', $status);
        }

        $groups = $query->latest()->paginate(10);

        return view('groups.index', compact('groups', 'status'));
    }

    public function create()
    {
        $allMembers = User::where('role', 'member')->where('is_active', true)->orderBy('name')->get();
        return view('groups.create', compact('allMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contribution_amount' => 'required|numeric|min:1000',
            'period_type' => 'required|in:monthly,biweekly,weekly',
            'max_members' => 'required|integer|min:2|max:100',
            'start_date' => 'required|date',
            'late_fee_per_day' => 'nullable|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'winner_determination' => 'required|in:lottery,fixed_order',
            'only_paid_can_win' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:150',
            'qris_image' => 'nullable|image|max:2048',
            'member_ids' => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $qrisPath = null;
        if ($request->hasFile('qris_image')) {
            $qrisPath = $request->file('qris_image')->store('qris', 'public');
        }

        $group = ArisanGroup::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'description' => $validated['description'] ?? null,
            'admin_id' => Auth::id(),
            'contribution_amount' => $validated['contribution_amount'],
            'period_type' => $validated['period_type'],
            'max_members' => $validated['max_members'],
            'start_date' => $validated['start_date'],
            'late_fee_per_day' => $validated['late_fee_per_day'] ?? 0,
            'grace_period_days' => $validated['grace_period_days'] ?? 3,
            'winner_determination' => $validated['winner_determination'],
            'only_paid_can_win' => $request->boolean('only_paid_can_win'),
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_no' => $validated['bank_account_no'] ?? null,
            'bank_account_name' => $validated['bank_account_name'] ?? null,
            'qris_image' => $qrisPath,
            'status' => 'draft',
        ]);

        // Attach initial members if selected
        if (!empty($validated['member_ids'])) {
            foreach ($validated['member_ids'] as $idx => $userId) {
                GroupMember::create([
                    'group_id' => $group->id,
                    'user_id' => $userId,
                    'join_date' => $group->start_date,
                    'fixed_order_number' => $idx + 1,
                    'has_won' => false,
                    'notification_channel' => 'whatsapp',
                    'is_active' => true,
                ]);
            }
        }

        ActivityLog::log('tambah_kelompok', "Membuat kelompok arisan baru: {$group->name}");

        return redirect()->route('groups.show', $group)->with('success', "Kelompok arisan '{$group->name}' berhasil dibuat! Silakan lengkapi anggota atau generate jadwal.");
    }

    public function show(ArisanGroup $group)
    {
        $group->load([
            'admin',
            'members.user',
            'members.wonRound',
            'rounds.host',
            'rounds.winner',
            'rounds.payments',
        ]);

        $activeMembers = $group->members()->where('is_active', true)->with('user')->get();
        $allNonMembers = User::where('role', 'member')
            ->where('is_active', true)
            ->whereNotIn('id', $group->members->pluck('user_id'))
            ->orderBy('name')
            ->get();

        $totalRounds = $group->rounds->count();
        $completedRounds = $group->rounds->where('status', 'completed')->count();
        $currentRound = $group->currentRound();

        // Financial summary for this group
        $totalCollected = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('amount');
        $totalPenalties = Payment::where('group_id', $group->id)->where('payment_status', 'paid')->sum('penalty_amount');
        $totalUnpaid = Payment::where('group_id', $group->id)->whereIn('payment_status', ['unpaid', 'late'])->sum('amount');
        $totalDisbursed = ArisanRound::where('group_id', $group->id)->where('prize_disbursed', true)->sum('winning_amount');

        return view('groups.show', compact(
            'group',
            'activeMembers',
            'allNonMembers',
            'totalRounds',
            'completedRounds',
            'currentRound',
            'totalCollected',
            'totalPenalties',
            'totalUnpaid',
            'totalDisbursed'
        ));
    }

    public function edit(ArisanGroup $group)
    {
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, ArisanGroup $group)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contribution_amount' => 'required|numeric|min:1000',
            'period_type' => 'required|in:monthly,biweekly,weekly',
            'max_members' => 'required|integer|min:2|max:100',
            'start_date' => 'required|date',
            'late_fee_per_day' => 'nullable|numeric|min:0',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'winner_determination' => 'required|in:lottery,fixed_order',
            'only_paid_can_win' => 'nullable|boolean',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:150',
            'qris_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,active,completed',
        ]);

        if ($request->hasFile('qris_image')) {
            $validated['qris_image'] = $request->file('qris_image')->store('qris', 'public');
        }
        $validated['only_paid_can_win'] = $request->boolean('only_paid_can_win');

        $group->update($validated);

        ActivityLog::log('edit_kelompok', "Mengubah data kelompok arisan {$group->name}");

        return redirect()->route('groups.show', $group)->with('success', 'Informasi kelompok arisan berhasil diperbarui!');
    }

    public function destroy(ArisanGroup $group)
    {
        $name = $group->name;
        $group->delete();

        ActivityLog::log('hapus_kelompok', "Menghapus kelompok arisan {$name}");

        return redirect()->route('groups.index')->with('success', "Kelompok arisan '{$name}' telah dihapus.");
    }

    public function generateSchedule(Request $request, ArisanGroup $group)
    {
        try {
            $count = ArisanService::generateRounds($group);
            return back()->with('success', "Alhamdulillah! Berhasil membuat {$count} putaran jadwal arisan otomatis beserta daftar tagihan iuran.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
