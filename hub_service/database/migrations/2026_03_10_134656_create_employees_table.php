<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // Id will be inserted manually from hr_service

            $table->string('name');
            $table->string('last_name');

            $table->decimal('salary', 12, 2);

            $table->string('country');

            $table->string('ssn')->nullable();
            $table->string('address')->nullable();

            $table->string('goal')->nullable();
            $table->string('tax_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
