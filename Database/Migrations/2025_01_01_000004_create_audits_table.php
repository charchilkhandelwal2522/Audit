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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('audit_template_id');
            $table->foreign('audit_template_id')->references('id')->on('audit_templates')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('department_id')->nullable()->comment('Department being audited');
            $table->foreign('department_id')->references('id')->on('teams')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedInteger('auditor_id')->comment('User conducting the audit');
            $table->foreign('auditor_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedInteger('auditee_id')->nullable()->comment('Person being audited');
            $table->foreign('auditee_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('location')->nullable()->comment('Store/Location where audit is conducted');
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_seconds')->nullable()->comment('Total time spent in seconds');
            $table->decimal('score', 5, 2)->nullable()->comment('Final audit score percentage');
            $table->integer('total_checkpoints')->default(0);
            $table->integer('completed_checkpoints')->default(0);
            $table->integer('partially_completed_checkpoints')->default(0);
            $table->text('summary')->nullable();
            $table->string('report_pdf')->nullable()->comment('Path to generated PDF report');
            $table->unsignedInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};

