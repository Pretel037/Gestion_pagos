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
            $table->unsignedBigInteger('voucher_id')->after('id'); // Añadir columna
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade'); // Añadir clave foránea
        });
    }

    public function down()
    {
        Schema::table('vouchers_validados', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']); // Quitar clave foránea
            $table->dropColumn('voucher_id'); // Quitar columna
        });
    }
};
