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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // ID del usuario
            $table->string('name'); // Nombre del usuario
            $table->string('email')->unique(); // Correo electrónico único
            $table->timestamp('email_verified_at')->nullable(); // Fecha de verificación del email
            $table->string('password'); // Contraseña del usuario
            $table->rememberToken(); // Token para recordar la sesión
            $table->enum('role', ['admin', 'teacher', 'student']); // Rol del usuario
            $table->string('codigo')->nullable(); // Código único de estudiante asignado por el admin
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null'); // Relación opcional con la organización
            $table->timestamps(); // Fechas de creación y actualización
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary(); // Email como clave primaria
            $table->string('token'); // Token de restablecimiento
            $table->timestamp('created_at')->nullable(); // Fecha de creación del token
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary(); // ID de la sesión
            $table->foreignId('user_id')->nullable()->index(); // Relación opcional con el usuario
            $table->string('ip_address', 45)->nullable(); // Dirección IP del usuario
            $table->text('user_agent')->nullable(); // User agent del navegador
            $table->longText('payload'); // Datos de la sesión
            $table->integer('last_activity')->index(); // Última actividad
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};