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
    Schema::create('user_invitations', function (Blueprint $table) {
        $table->id(); // ID único de la invitación
        $table->string('email')->unique(); // El email del invitado (único para no spamear)
        $table->string('token', 60)->unique(); // El token secreto del enlace (60 caracteres, único)
        $table->timestamp('created_at')->useCurrent(); // Para saber cuándo se creó (y si ha caducado)
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invitations');
    }
};
