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
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('arisan_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('join_date')->nullable();
            $table->integer('fixed_order_number')->nullable(); // nomor antrian/urutan tetap jika dipilih
            $table->boolean('has_won')->default(false);
            $table->foreignId('won_round_id')->nullable()->constrained('arisan_rounds')->onDelete('set null');
            $table->string('notification_channel')->default('whatsapp'); // whatsapp, app, email
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
