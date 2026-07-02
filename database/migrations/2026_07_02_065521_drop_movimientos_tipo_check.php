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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE movimientos DROP CONSTRAINT IF EXISTS movimientos_tipo_check');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
