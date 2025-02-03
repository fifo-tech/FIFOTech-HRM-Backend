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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('branch')->nullable();

            // Adding emergency contact details columns
            $table->string('emg_contact_name')->nullable();
            $table->string('emg_relationship')->nullable();
            $table->string('emg_phone_number')->nullable();
            $table->text('emg_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_number',
                'branch',
                'emg_contact_name',
                'emg_relationship',
                'emg_phone_number',
                'emg_address'
            ]);
        });
    }
};
