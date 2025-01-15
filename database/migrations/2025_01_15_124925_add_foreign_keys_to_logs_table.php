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
        Schema::table('logs', function (Blueprint $table) {
            $table->foreign(['utilisateur_id'], 'logs_ibfk_1')->references(['id'])->on('utilisateurs')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['fonctionnalite_id'], 'logs_ibfk_2')->references(['id'])->on('fonctionnalites')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign('logs_ibfk_1');
            $table->dropForeign('logs_ibfk_2');
        });
    }
};
