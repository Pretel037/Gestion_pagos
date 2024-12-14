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
        Schema::dropIfExists('vouchers_validados');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('pagos_s_i_g_g_a_s');
    }

    public function down()
    {
        // Aquí puedes agregar las migraciones para volver a crear las tablas si es necesario
    }
};
