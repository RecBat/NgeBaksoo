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
        Schema::create('order_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_detail_id')->constrained()->cascadeOnDelete();
            $table->foreignId('option_choice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('extra_price')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_customizations');
    }
};
