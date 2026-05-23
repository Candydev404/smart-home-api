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
        Schema::create('energy_logs', function (Blueprint $table) {
            $table->id();
            $table->string('appliance_name');
            $table->integer('wattage'); // e.g., 15 for a lightbulb
            $table->timestamp('turned_on_at');
            $table->timestamp('turned_off_at')->nullable();
            $table->decimal('total_kwh', 8, 4)->nullable(); //Calculated when tured off 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('energy_logs');
    }
};
