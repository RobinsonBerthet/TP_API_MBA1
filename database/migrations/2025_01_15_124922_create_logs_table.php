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
        Schema::create('logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('date_action')->useCurrent();
            $table->integer('utilisateur_id')->nullable()->index('utilisateur_id');
            $table->integer('fonctionnalite_id')->nullable()->index('fonctionnalite_id');
            $table->text('description_action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
