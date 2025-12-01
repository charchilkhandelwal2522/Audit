<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to use raw SQL to alter enum column to be nullable
        DB::statement("ALTER TABLE `audit_checkpoint_responses` MODIFY `status` ENUM('not_completed', 'partially_completed', 'completed') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to NOT NULL with default
        DB::statement("ALTER TABLE `audit_checkpoint_responses` MODIFY `status` ENUM('not_completed', 'partially_completed', 'completed') NOT NULL DEFAULT 'not_completed'");
    }
};
