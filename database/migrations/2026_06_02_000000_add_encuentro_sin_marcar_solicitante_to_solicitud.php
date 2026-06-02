<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            if (!Schema::hasColumn('solicitud', 'encuentro_sin_marcar_solicitante')) {
                $table->boolean('encuentro_sin_marcar_solicitante')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('solicitud', function (Blueprint $table) {
            if (Schema::hasColumn('solicitud', 'encuentro_sin_marcar_solicitante')) {
                $table->dropColumn('encuentro_sin_marcar_solicitante');
            }
        });
    }
};
