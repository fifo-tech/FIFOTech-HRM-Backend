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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('date'); // Date of attendance
            $table->enum('status', ['Present', 'Absent', 'On Leave'])->default('Absent'); // Attendance status
            $table->time('clock_in')->nullable(); // Time of clock-in
            $table->text('clock_in_reason')->nullable(); // Reason for late clock-in
            $table->time('clock_out')->nullable(); // Time of clock-out
            $table->text('clock_out_reason')->nullable(); // Reason for early clock-out
            $table->time('early_leaving')->nullable(); // Early leaving time
            $table->time('total_work_hour')->nullable(); // Total hours worked

            // Additional fields for IP address, device, and location
            $table->string('ip_address')->nullable(); // IP address of the request
            $table->string('device')->nullable(); // Device used for attendance
            $table->string('location')->nullable(); // Geolocation of the user
            $table->string('fingerprint_id')->nullable(); // Fingerprint ID from the device
            $table->boolean('fingerprint_verified')->default(false); // Whether the fingerprint was verified
            $table->timestamp('fingerprint_scan_time')->nullable(); // Timestamp of when the fingerprint was scanned
            $table->string('fingerprint_device_id')->nullable(); // ID of the fingerprint device used


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
