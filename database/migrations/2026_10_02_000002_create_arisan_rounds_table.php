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
        Schema::create('arisan_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('arisan_groups')->onDelete('cascade');
            $table->integer('round_number'); // Putaran ke-1, ke-2, dst
            $table->date('due_date'); // Tanggal jatuh tempo iuran
            $table->date('draw_date'); // Tanggal pengocokan / kumpul arisan
            $table->foreignId('host_user_id')->nullable()->constrained('users')->onDelete('set null'); // Tuan Rumah
            $table->string('host_location')->nullable(); // Lokasi arisan (e.g. Rumah Bu RT / Balai RW)
            $table->foreignId('winner_user_id')->nullable()->constrained('users')->onDelete('set null'); // Pemenang
            $table->decimal('winning_amount', 15, 2)->nullable(); // Total uang arisan yang didapat
            $table->boolean('prize_disbursed')->default(false); // Status pencairan dana
            $table->dateTime('disbursed_at')->nullable(); // Waktu penyerahan uang
            $table->string('disbursement_proof')->nullable(); // Foto serah terima / kwitansi
            $table->text('disbursement_notes')->nullable(); // Catatan serah terima
            $table->string('status')->default('pending'); // pending, ongoing, completed
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arisan_rounds');
    }
};
