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
        Schema::create('audit_checkpoint_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->foreign('audit_id')->references('id')->on('audits')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('checkpoint_id');
            $table->foreign('checkpoint_id')->references('id')->on('audit_template_checkpoints')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('status', ['not_completed', 'partially_completed', 'completed'])->default('not_completed');
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_checkpoint_responses');
    }
};

