<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('lembretes', 'origem')) {
            Schema::table('lembretes', function (Blueprint $table) {
                $table->string('origem', 30)
                    ->default('manual')
                    ->after('status');
            });
        }

        DB::table('lembretes')
            ->where('tipo', 'medicamento')
            ->update(['tipo' => 'medicacao']);

        DB::table('lembretes')
            ->where('tipo', 'outros')
            ->update(['tipo' => 'outro']);
    }

    public function down(): void
    {
        if (Schema::hasColumn('lembretes', 'origem')) {
            Schema::table('lembretes', function (Blueprint $table) {
                $table->dropColumn('origem');
            });
        }
    }
};
