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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary(); // Unique ID for each notification
            $table->string('type'); // Notification class name
            $table->morphs('notifiable'); // Polymorphic: user_id, user_type
            $table->text('data'); // JSON data (title, message, etc.)
            $table->timestamp('read_at')->nullable(); // null = unread
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
