<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\NotificationLog;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Admin
        $admin = User::create([
            'name' => 'Ibu Hj. Siti Aminah (Ketua PKK)',
            'email' => 'admin@pkkkarangkedawung.id',
            'phone' => '081234567890',
            'role' => 'admin',
            'address' => 'Jl. Melati No. 12, RT 02 / RW 03 Desa KarangKedawung',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // 2. Create Members
        $membersData = [
            ['name' => 'Ibu Endang Rahayu (Bendahara)', 'phone' => '081234567891', 'email' => 'endang@gmail.com', 'address' => 'RT 02 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Sri Wahyuni (Sekretaris)', 'phone' => '081234567892', 'email' => 'sriwahyuni@gmail.com', 'address' => 'RT 01 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Ratna Dewi', 'phone' => '081234567893', 'email' => 'ratna@gmail.com', 'address' => 'RT 03 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Anisa Putri', 'phone' => '081234567894', 'email' => 'anisa@gmail.com', 'address' => 'RT 02 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Dewi Lestari', 'phone' => '081234567895', 'email' => 'dewi@gmail.com', 'address' => 'RT 01 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Nurul Hidayah', 'phone' => '081234567896', 'email' => 'nurul@gmail.com', 'address' => 'RT 04 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Tri Handayani', 'phone' => '081234567897', 'email' => 'tri@gmail.com', 'address' => 'RT 03 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Yuliana Sari', 'phone' => '081234567898', 'email' => 'yuliana@gmail.com', 'address' => 'RT 02 / RW 03 KarangKedawung'],
            ['name' => 'Ibu Kartika Wulandari', 'phone' => '081234567899', 'email' => 'kartika@gmail.com', 'address' => 'RT 01 / RW 03 KarangKedawung'],
        ];

        $users = [$admin];
        foreach ($membersData as $m) {
            $u = User::create([
                'name' => $m['name'],
                'phone' => $m['phone'],
                'email' => $m['email'],
                'role' => 'member',
                'address' => $m['address'],
                'password' => Hash::make('password'),
                'is_active' => true,
            ]);
            $users[] = $u;
        }

        // 3. Create Group 1: Arisan Melati Utama (Bulanan, 10 Anggota, 100k)
        $now = Carbon::now();
        $group1 = ArisanGroup::create([
            'name' => 'Arisan Guyub Rukun Melati RW 03',
            'slug' => 'arisan-guyub-rukun-melati-rw-03',
            'description' => 'Arisan bulanan ibu-ibu PKK RW 03 KarangKedawung. Pertemuan rutin diadakan setiap hari Minggu pertama di awal bulan.',
            'admin_id' => $admin->id,
            'contribution_amount' => 100000,
            'period_type' => 'monthly',
            'max_members' => 10,
            'start_date' => $now->copy()->subMonth()->startOfMonth()->format('Y-m-d'),
            'late_fee_per_day' => 2000,
            'grace_period_days' => 3,
            'winner_determination' => 'lottery',
            'only_paid_can_win' => true,
            'bank_name' => 'Bank BCA (Kas PKK Melati)',
            'bank_account_no' => '8830192837',
            'bank_account_name' => 'Ibu Endang Rahayu',
            'status' => 'active',
        ]);

        // Register 10 members in Group 1
        foreach ($users as $index => $u) {
            GroupMember::create([
                'group_id' => $group1->id,
                'user_id' => $u->id,
                'join_date' => $group1->start_date,
                'fixed_order_number' => $index + 1,
                'has_won' => false,
                'notification_channel' => 'whatsapp',
                'is_active' => true,
            ]);
        }

        // Generate rounds for Group 1
        ArisanService::generateRounds($group1);

        // Putaran 1: Mark as completed with winner Ibu Sri Wahyuni
        $round1 = ArisanRound::where('group_id', $group1->id)->where('round_number', 1)->first();
        $winner1 = $users[2]; // Ibu Sri Wahyuni

        if ($round1) {
            $round1->update([
                'status' => 'completed',
                'winner_user_id' => $winner1->id,
                'winning_amount' => 1000000,
                'prize_disbursed' => true,
                'disbursed_at' => Carbon::parse($round1->draw_date)->setTime(11, 0),
                'disbursement_notes' => 'Uang tunai Rp 1.000.000 diserahkan langsung pada pertemuan PKK di Balai RW 03.',
            ]);

            // Mark Sri Wahyuni as won
            GroupMember::where('group_id', $group1->id)->where('user_id', $winner1->id)->update([
                'has_won' => true,
                'won_round_id' => $round1->id,
            ]);

            // Mark all round 1 payments as paid
            Payment::where('round_id', $round1->id)->update([
                'payment_status' => 'paid',
                'paid_at' => Carbon::parse($round1->due_date)->subDay(),
                'verified_at' => Carbon::parse($round1->due_date),
                'verified_by' => $admin->id,
            ]);
        }

        // Putaran 2: Mark as Ongoing, some paid, some unpaid
        $round2 = ArisanRound::where('group_id', $group1->id)->where('round_number', 2)->first();
        if ($round2) {
            $round2->update(['status' => 'ongoing']);

            // Set 5 members paid
            $paidUsers = [$users[0], $users[1], $users[2], $users[3], $users[4]];
            foreach ($paidUsers as $pu) {
                Payment::where('round_id', $round2->id)->where('user_id', $pu->id)->update([
                    'payment_status' => 'paid',
                    'paid_at' => Carbon::now()->subDays(2),
                    'verified_at' => Carbon::now()->subDay(),
                    'verified_by' => $admin->id,
                ]);
            }

            // Set 1 member pending verification
            $pendingUser = $users[5];
            Payment::where('round_id', $round2->id)->where('user_id', $pendingUser->id)->update([
                'payment_status' => 'pending_verification',
                'paid_at' => Carbon::now()->subHours(3),
                'user_notes' => 'Sudah transfer via BCA Mobile atas nama Bpk. Slamet (Suami). Mohon dicek ya Bu.',
            ]);
        }

        // 4. Create Group 2: Arisan Mawar Cantik RT 01 (Urutan Tetap, 50k)
        $group2 = ArisanGroup::create([
            'name' => 'Arisan Mawar Cantik PKK RT 01',
            'slug' => 'arisan-mawar-cantik-pkk-rt-01',
            'description' => 'Arisan RT 01 sistem urutan tetap. Sangat cocok untuk silaturahmi bulanan.',
            'admin_id' => $admin->id,
            'contribution_amount' => 50000,
            'period_type' => 'monthly',
            'max_members' => 6,
            'start_date' => $now->copy()->startOfMonth()->format('Y-m-d'),
            'late_fee_per_day' => 1000,
            'grace_period_days' => 3,
            'winner_determination' => 'fixed_order',
            'only_paid_can_win' => true,
            'bank_name' => 'Bank BRI (Kas PKK RT 01)',
            'bank_account_no' => '034101002938501',
            'bank_account_name' => 'Ibu Siti Aminah',
            'status' => 'active',
        ]);

        for ($i = 0; $i < 6; $i++) {
            GroupMember::create([
                'group_id' => $group2->id,
                'user_id' => $users[$i]->id,
                'join_date' => $group2->start_date,
                'fixed_order_number' => $i + 1,
                'has_won' => false,
                'notification_channel' => 'whatsapp',
                'is_active' => true,
            ]);
        }
        ArisanService::generateRounds($group2);

        // Activity Logs
        ActivityLog::log('inisialisasi', 'Sistem Arisan PKK KarangKedawung berhasil dipasang & diinisialisasi.', $admin->id);
    }
}
