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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('arisan_groups')->onDelete('cascade');
            $table->foreignId('round_id')->nullable()->constrained('arisan_rounds')->onDelete('cascade');
            $table->string('type'); // reminder_h3, reminder_h1, reminder_h0, late_penalty, payment_verified, draw_reminder, winner_announcement, disbursement_info
            $table->string('title');
            $table->text('message');
            $table->string('channel')->default('whatsapp'); // whatsapp, app, email
            $table->string('status')->default('sent'); // pending, sent, failed, read
            $table->dateTime('sent_at')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->text('whatsapp_link')->nullable(); // wa.me pre-generated link
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
