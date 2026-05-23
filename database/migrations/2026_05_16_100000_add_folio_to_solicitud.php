<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->string('folio', 20)->nullable()->unique()->after('id_solicitud')
                  ->comment('Folio único de la solicitud formato VIS-XXXX-XXXX');
            $table->unsignedInteger('cancelado_por')->nullable()
                  ->comment('ID del empleado SAM que canceló');
            $table->timestamp('fecha_cancelacion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            $table->dropColumn(['folio', 'cancelado_por', 'fecha_cancelacion']);
        });
    }
};