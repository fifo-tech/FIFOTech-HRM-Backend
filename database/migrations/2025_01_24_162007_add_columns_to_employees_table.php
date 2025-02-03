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
            $table->date('contract_date')->nullable();
            $table->date('contract_end')->nullable();
            $table->text('leave_categories')->nullable();
            $table->text('role_description')->nullable();
            $table->string('citizenship')->nullable();
            $table->string('github')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'contract_date',
                'contract_end',
                'leave_categories',
                'role_description',
                'github',
                'citizenship',
            ]);
        });
    }
};
