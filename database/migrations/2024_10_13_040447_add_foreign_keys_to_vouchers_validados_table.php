<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('vouchers_validados', function (Blueprint $table) {
            // Agregar columnas para las claves foráneas
         
           // $table->foreignId('pago_id')->constrained('pagos_s_i_g_g_a_s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('vouchers_validados', function (Blueprint $table) {
            // Eliminar las claves foráneas
           // $table->dropForeign(['voucher_id']);
            //$table->dropColumn('voucher_id');
            
            $table->dropForeign(['pago_id']);
            $table->dropColumn('pago_id');
        });
    }
};
