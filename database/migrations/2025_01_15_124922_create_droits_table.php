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
        Schema::create('droits', function (Blueprint $table) {
            $table->integer('id', true);
            $table->text('description')->nullable();
            $table->integer('fonctionnalite_id')->nullable()->unique('unique_fonctionnalite_role');
            $table->integer('role_id')->nullable()->index('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('droits');
    }
};
