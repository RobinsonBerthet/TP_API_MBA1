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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('nom', 100);
            $table->string('email', 100)->unique('email');
            $table->string('motDePasse');
            $table->integer('role_id')->nullable()->index('utilisateur_ibfk_1');
            $table->timestamp('dateCreation')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
