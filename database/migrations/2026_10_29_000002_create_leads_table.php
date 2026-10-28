<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->restrictOnDelete();
            $table->string('full_name', 200);
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('service_interest', 60)->nullable();
            $table->string('status', 40)->default('new')->index();
            $table->string('source', 80)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['office_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
