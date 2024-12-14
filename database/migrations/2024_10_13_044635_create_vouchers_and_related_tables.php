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
            $table->dateTime('fecha'); // Fecha del voucher
            $table->time('hora'); // Hora del voucher
            $table->string('operacion'); // Número de operación
            $table->decimal('monto', 8, 2); // Monto del voucher
            $table->string('codigo_dni'); // Código de DNI
            $table->string('servicio'); // Servicio asociado al voucher
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
           
            $table->timestamps();

            // Definición de la clave foránea
           
        });

        // Crear la tabla pagos_s_i_g_g_a_s
        Schema::create('pagos_s_i_g_g_a_s', function (Blueprint $table) {
            $table->id();
            $table->string('detalle_pago');
            $table->decimal('monto_pago', 8, 2);
            $table->dateTime('fecha_pago');
            $table->unsignedBigInteger('voucher_id'); // Clave foránea para vouchers
            $table->unsignedBigInteger('vouchers_validados_id'); // Clave foránea para vouchers_validados
            $table->timestamps();

            // Definición de las claves foráneas
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->foreign('vouchers_validados_id')->references('id')->on('vouchers_validados')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pagos_s_i_g_g_a_s');
        Schema::dropIfExists('vouchers_validados');
        Schema::dropIfExists('vouchers');
    }
};
