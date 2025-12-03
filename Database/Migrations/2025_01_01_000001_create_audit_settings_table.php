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
        Schema::create('audit_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->decimal('partial_completion_weight', 3, 2)->default(0.50)->comment('Weight for partial completion (0-1)');
            $table->integer('score_threshold_alert')->default(70)->comment('Minimum score threshold for alerts');
            $table->boolean('send_result_to_manager')->default(true);
            $table->boolean('send_result_to_auditee')->default(true);
            $table->boolean('send_result_to_auditor')->default(true);
            $table->boolean('generate_pdf_report')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_settings');
    }
};

