<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Crear la tabla vouchers
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->decimal('monto', 8, 2);
            $table->timestamps();
        });

        // Crear la tabla vouchers_validados
        Schema::create('vouchers_validados', function (Blueprint $table) {
            $table->id();
            $table->string('numero_operacion');
            $table->dateTime('fecha_pago');
            $table->decimal('monto', 8, 2);
            $table->string('dni_codigo');
            $table->string('nombres');
            $table->string('nombre_curso_servicio');
            $table->unsignedBigInteger('voucher_id'); // Clave foránea para vouchers
            $table->timestamps();

            // Definición de la clave foránea
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
        });

        // Crear la tabla pagos_s_i_g_g_a_s
        Schema::create('pagos_s_i_g_g_a_s', function (Blueprint $table) {
            $table->id();
            $table->string('detalle_pago');
            $table->decimal('monto_pago', 8, 2);
            $table->dateTime('fecha_pago');
            $table->unsignedBigInteger('voucher_id'); // Clave foránea para vouchers
            $table->timestamps();

            // Definición de la clave foránea
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_s_i_g_g_a_s');
        Schema::dropIfExists('vouchers_validados');
        Schema::dropIfExists('vouchers');
    }
};
