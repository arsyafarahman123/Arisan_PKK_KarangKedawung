<?php

namespace Tests\Feature;

use App\Models\ArisanGroup;
use App\Models\ArisanRound;
use App\Models\GroupMember;
use App\Models\Payment;
use App\Models\User;
use App\Services\ArisanService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ArisanPkkTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $member;
    protected ArisanGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Admin
        $this->admin = User::create([
            'name' => 'Ibu Ketua Admin',
            'email' => 'admin@pkkkarangkedawung.id',
            'phone' => '081234567890',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // 2. Create Members
        $this->member = User::create([
            'name' => 'Ibu Endang Rahayu',
            'email' => 'endang@gmail.com',
            'phone' => '081234567891',
            'role' => 'member',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $member2 = User::create([
            'name' => 'Ibu Sri Wahyuni',
            'email' => 'sri@gmail.com',
            'phone' => '081234567892',
            'role' => 'member',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        // 3. Create Group
        $this->group = ArisanGroup::create([
            'name' => 'Arisan Melati RW 03',
            'slug' => 'arisan-melati-rw-03',
            'admin_id' => $this->admin->id,
            'contribution_amount' => 100000,
            'period_type' => 'monthly',
            'max_members' => 10,
            'start_date' => now()->toDateString(),
            'late_fee_per_day' => 2000,
            'grace_period_days' => 3,
            'winner_determination' => 'lottery',
            'only_paid_can_win' => true,
            'status' => 'active',
        ]);

        GroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->admin->id,
            'fixed_order_number' => 1,
            'has_won' => false,
        ]);

        GroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $this->member->id,
            'fixed_order_number' => 2,
            'has_won' => false,
        ]);

        GroupMember::create([
            'group_id' => $this->group->id,
            'user_id' => $member2->id,
            'fixed_order_number' => 3,
            'has_won' => false,
        ]);
    }

    public function test_login_with_phone_number(): void
    {
        $response = $this->post('/login', [
            'login_id' => '081234567890',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_generate_rounds_creates_rounds_and_bills(): void
    {
        $roundsCount = ArisanService::generateRounds($this->group);

        $this->assertEquals(3, $roundsCount);
        $this->assertDatabaseCount('arisan_rounds', 3);
        $this->assertDatabaseCount('payments', 9); // 3 members * 3 rounds
    }

    public function test_admin_can_verify_payment(): void
    {
        ArisanService::generateRounds($this->group);
        $payment = Payment::where('user_id', $this->member->id)->first();

        $response = $this->actingAs($this->admin)->post("/payments/{$payment->id}/verify", [
            'action' => 'approve',
            'admin_notes' => 'Lunas via transfer BCA',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('paid', $payment->fresh()->payment_status);
        $this->assertEquals($this->admin->id, $payment->fresh()->verified_by);
    }

    public function test_lottery_draw_selects_winner_and_updates_round(): void
    {
        ArisanService::generateRounds($this->group);
        $round = $this->group->rounds()->first();

        // Mark all payments as paid so they are eligible
        Payment::where('round_id', $round->id)->update(['payment_status' => 'paid']);

        $winner = ArisanService::executeDraw($round);

        $this->assertNotNull($winner);
        $this->assertEquals('completed', $round->fresh()->status);
        $this->assertEquals($winner->id, $round->fresh()->winner_user_id);
        $this->assertTrue(GroupMember::where('group_id', $this->group->id)->where('user_id', $winner->id)->first()->has_won);
    }

    public function test_reschedule_round_shifts_dates(): void
    {
        ArisanService::generateRounds($this->group);
        $round1 = $this->group->rounds()->where('round_number', 1)->first();
        $round2 = $this->group->rounds()->where('round_number', 2)->first();

        $newDueDate = now()->addDays(5)->format('Y-m-d');
        $newDrawDate = now()->addDays(7)->format('Y-m-d');

        ArisanService::rescheduleRound($round1, $newDueDate, $newDrawDate, true);

        $this->assertEquals($newDueDate, $round1->fresh()->due_date->format('Y-m-d'));
        $this->assertEquals($newDrawDate, $round1->fresh()->draw_date->format('Y-m-d'));
    }

    public function test_whatsapp_link_generation(): void
    {
        $link = WhatsAppService::createWaLink('081234567890', 'Halo Ibu!');
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $link);
    }

    public function test_export_csv_and_print_view(): void
    {
        ArisanService::generateRounds($this->group);

        $printResponse = $this->actingAs($this->admin)->get("/reports/print/{$this->group->id}");
        $printResponse->assertStatus(200);
        $printResponse->assertSee($this->group->name);

        $csvResponse = $this->actingAs($this->admin)->get("/reports/export-csv/{$this->group->id}");
        $csvResponse->assertStatus(200);
    }
}
