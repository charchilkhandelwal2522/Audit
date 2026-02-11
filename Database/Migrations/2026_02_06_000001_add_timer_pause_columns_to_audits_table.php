<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds pause/resume support for audit timer: stop on Save & Exit, resume on Continue.
     */
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->unsignedInteger('total_elapsed_seconds')->default(0)->after('started_at')
                ->comment('Cumulative seconds the timer has been running (paused when user saves and exits)');
            $table->timestamp('resumed_at')->nullable()->after('total_elapsed_seconds')
                ->comment('When the current session started (null when timer is paused)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropColumn(['total_elapsed_seconds', 'resumed_at']);
        });
    }
};
