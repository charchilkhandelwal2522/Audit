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
        Schema::create('audit_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('department_id')->nullable()->comment('Team/Department this template belongs to');
            $table->foreign('department_id')->references('id')->on('teams')->onDelete('set null')->onUpdate('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('added_by')->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->unsignedInteger('last_updated_by')->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_templates');
    }
};

