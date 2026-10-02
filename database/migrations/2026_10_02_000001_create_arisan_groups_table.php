<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arisan_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->decimal('contribution_amount', 15, 2); // nominal iuran per putaran
            $table->string('period_type')->default('monthly'); // monthly, biweekly, weekly
            $table->integer('max_members'); // jumlah kuota anggota
            $table->date('start_date'); // tanggal mulai
            $table->decimal('late_fee_per_day', 15, 2)->default(0); // denda per hari telat
            $table->integer('grace_period_days')->default(3); // toleransi hari keterlambatan
            $table->string('winner_determination')->default('lottery'); // lottery (kocok), fixed_order (urutan tetap)
            $table->boolean('only_paid_can_win')->default(true); // hanya anggota lunas yang ikut kocok
            $table->string('bank_name')->nullable(); // e.g. Kas PKK / BCA / BRI
            $table->string('bank_account_no')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('qris_image')->nullable();
            $table->string('status')->default('draft'); // draft, active, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arisan_groups');
    }
};
