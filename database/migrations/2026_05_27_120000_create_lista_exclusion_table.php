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
        Schema::create('lista_exclusion', function (Blueprint $table) {
            $table->bigIncrements('id_lista_exclusion');
            $table->unsignedBigInteger('id_visitante');
            $table->unsignedBigInteger('id_autorizador')->nullable();
            $table->text('motivo_exclusion');
            $table->timestamp('fecha_bloqueo')->useCurrent();

            // Indexes for faster lookups
            $table->index('id_visitante');
            $table->index('id_autorizador');

            // Optional foreign keys if the related tables exist
            // Schema::table('lista_exclusion', function (Blueprint $table) {
            //     $table->foreign('id_visitante')->references('id_visitante')->on('visitante')->onDelete('cascade');
            //     $table->foreign('id_autorizador')->references('id')->on('users')->onDelete('set null');
            // });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lista_exclusion');
    }
};
