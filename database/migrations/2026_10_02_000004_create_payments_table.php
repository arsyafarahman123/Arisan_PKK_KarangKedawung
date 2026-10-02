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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained('arisan_rounds')->onDelete('cascade');
            $table->foreignId('group_id')->constrained('arisan_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 15, 2); // nominal pokok iuran
            $table->decimal('penalty_amount', 15, 2)->default(0); // denda jika terlambat
            $table->decimal('total_amount', 15, 2); // amount + penalty
            $table->date('due_date'); // tanggal jatuh tempo
            $table->string('payment_status')->default('unpaid'); // unpaid, pending_verification, paid, late
            $table->string('payment_method')->default('transfer'); // cash, transfer, qris
            $table->string('proof_image')->nullable(); // bukti transfer
            $table->dateTime('paid_at')->nullable(); // waktu anggota upload / bayar
            $table->dateTime('verified_at')->nullable(); // waktu admin verifikasi
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('rejection_reason')->nullable(); // jika pembayaran ditolak
            $table->text('user_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->unique(['round_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
