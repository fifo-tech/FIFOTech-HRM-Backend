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
        Schema::table('leave_requests', function (Blueprint $table) {
            // Drop the foreign key and the column
            $table->dropForeign(['approved_by']);
            $table->dropColumn('approved_by');

            // Add the new string column
            $table->string('approved_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            // Reverse: Drop the string column and re-add the foreign key
            $table->dropColumn('approved_by');
            $table->foreignId('approved_by')->nullable()->constrained('employees')->onDelete('set null');
        });
    }
};
