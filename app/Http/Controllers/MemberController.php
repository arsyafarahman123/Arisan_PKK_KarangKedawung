<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\GroupMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $status = $request->get('status');

        $query = User::with(['groupMemberships.group', 'groupMemberships.wonRound']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', (bool)$status);
        }

        $members = $query->orderBy('name')->paginate(12);

        return view('members.index', compact('members', 'search', 'status'));
    }

    public function create()
    {
        $groups = ArisanGroup::where('status', '!=', 'completed')->get();
        return view('members.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|email|max:255|unique:users,email',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,member',
            'password' => 'nullable|string|min:6',
            'group_id' => 'nullable|exists:arisan_groups,id',
            'notification_channel' => 'nullable|in:whatsapp,app,email',
        ]);

        $password = !empty($validated['password']) ? $validated['password'] : 'password';

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        // If assigned to a group right away
        if (!empty($validated['group_id'])) {
            $group = ArisanGroup::findOrFail($validated['group_id']);
            $currentCount = $group->members()->count();

            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'join_date' => now()->toDateString(),
                'fixed_order_number' => $currentCount + 1,
                'has_won' => false,
                'notification_channel' => $validated['notification_channel'] ?? 'whatsapp',
                'is_active' => true,
            ]);
        }

        ActivityLog::log('tambah_anggota', "Menambahkan anggota baru: {$user->name} ({$user->phone})");

        return redirect()->route('members.index')->with('success', "Anggota Ibu '{$user->name}' berhasil ditambahkan ke dalam sistem!");
    }

    public function edit(User $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, User $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $member->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $member->id,
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,member',
            'password' => 'nullable|string|min:6',
            'is_active' => 'required|boolean',
        ]);

        $member->name = $validated['name'];
        $member->phone = $validated['phone'];
        $member->email = $validated['email'] ?? null;
        $member->address = $validated['address'] ?? null;
        $member->role = $validated['role'];
        $member->is_active = (bool)$validated['is_active'];

        if (!empty($validated['password'])) {
            $member->password = Hash::make($validated['password']);
        }

        $member->save();

        ActivityLog::log('edit_anggota', "Mengubah data anggota: {$member->name}");

        return redirect()->route('members.index')->with('success', "Data Ibu '{$member->name}' berhasil diperbarui.");
    }

    public function toggleStatus(User $member)
    {
        $member->is_active = !$member->is_active;
        $member->save();

        $statusStr = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::log('status_anggota', "Status anggota {$member->name} {$statusStr}");

        return back()->with('success', "Status keanggotaan Ibu {$member->name} berhasil {$statusStr}.");
    }

    public function addToGroup(Request $request, ArisanGroup $group)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'notification_channel' => 'nullable|in:whatsapp,app,email',
        ]);

        // Check if already in group
        $exists = GroupMember::where('group_id', $group->id)->where('user_id', $validated['user_id'])->first();
        if ($exists) {
            return back()->with('error', 'Anggota sudah terdaftar di kelompok ini.');
        }

        $user = User::findOrFail($validated['user_id']);
        $count = $group->members()->count();

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'join_date' => now()->toDateString(),
            'fixed_order_number' => $count + 1,
            'has_won' => false,
            'notification_channel' => $validated['notification_channel'] ?? 'whatsapp',
            'is_active' => true,
        ]);

        ActivityLog::log('tambah_anggota_kelompok', "Menambahkan {$user->name} ke kelompok {$group->name}");

        return back()->with('success', "Ibu {$user->name} berhasil didaftarkan ke kelompok {$group->name}!");
    }

    public function removeFromGroup(ArisanGroup $group, User $member)
    {
        GroupMember::where('group_id', $group->id)->where('user_id', $member->id)->delete();

        ActivityLog::log('hapus_anggota_kelompok', "Mengeluarkan {$member->name} dari kelompok {$group->name}");

        return back()->with('success', "Ibu {$member->name} berhasil dikeluarkan dari kelompok ini.");
    }

    public function updateGroupMembership(Request $request, GroupMember $membership)
    {
        $validated = $request->validate([
            'fixed_order_number' => 'nullable|integer|min:1',
            'notification_channel' => 'required|in:whatsapp,app,email',
            'is_active' => 'required|boolean',
            'notes' => 'nullable|string',
        ]);

        $membership->update($validated);

        return back()->with('success', 'Pengaturan keanggotaan berhasil diperbarui.');
    }
}
