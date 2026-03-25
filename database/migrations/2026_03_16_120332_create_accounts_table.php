<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('rbi')->unique(); 
            $table->enum('type', ['COURANT', 'EPARGNE', 'MINEUR']);
            $table->enum('status', ['ACTIVE', 'BLOCKED', 'CLOSED'])->default('ACTIVE');
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('overdraft_limit', 10, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->decimal('monthly_fee', 10, 2)->nullable();
            $table->string('block_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
