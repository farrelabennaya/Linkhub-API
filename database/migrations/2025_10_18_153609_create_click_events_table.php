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
        Schema::create('click_events', function (Blueprint $t) {
            $t->id();
            $t->foreignId('link_id')->constrained()->cascadeOnDelete();
            $t->string('ua', 255)->nullable();
            $t->string('ip_hash', 64)->nullable();
            $t->string('referer', 255)->nullable();
            $t->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('click_events');
    }
};
