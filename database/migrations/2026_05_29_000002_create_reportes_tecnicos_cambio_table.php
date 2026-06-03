<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes_tecnicos_cambio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_cambio_id')
                  ->constrained('solicitudes_cambio')
                  ->onDelete('cascade');
            $table->foreignId('developer_id')->constrained('users')->onDelete('cascade');
            $table->string('github_issue_id')->nullable();
            $table->string('git_branch')->nullable();
            $table->string('commit_hash')->nullable();
            $table->string('sandbox_status')->nullable();
            $table->text('risk_analysis')->nullable();
            $table->text('rollback_plan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes_tecnicos_cambio');
    }
};
