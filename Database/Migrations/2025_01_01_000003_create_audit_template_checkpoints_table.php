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
        Schema::create('audit_template_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_template_id');
            $table->foreign('audit_template_id')->references('id')->on('audit_templates')->onDelete('cascade')->onUpdate('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order')->default(0)->comment('Order of checkpoint in the template');
            $table->boolean('requires_file_upload')->default(false);
            $table->boolean('requires_photo')->default(false);
            $table->boolean('requires_notes')->default(false);
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_template_checkpoints');
    }
};

