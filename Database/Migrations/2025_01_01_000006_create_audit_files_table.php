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
        Schema::create('audit_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('audit_id');
            $table->foreign('audit_id')->references('id')->on('audits')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('checkpoint_response_id')->nullable();
            $table->foreign('checkpoint_response_id')->references('id')->on('audit_checkpoint_responses')->onDelete('cascade')->onUpdate('cascade');
            $table->string('filename');
            $table->string('hashname');
            $table->string('file_type')->nullable()->comment('image, document, etc.');
            $table->bigInteger('size')->nullable()->comment('File size in bytes');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('audit_files');
    }
};

