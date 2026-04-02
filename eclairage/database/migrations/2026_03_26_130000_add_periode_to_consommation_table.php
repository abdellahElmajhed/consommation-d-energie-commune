<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consommation', function (Blueprint $table) {
            $table->string('periode')->nullable()->after('c_dhs');
        });
    }

    public function down(): void
    {
        Schema::table('consommation', function (Blueprint $table) {
            $table->dropColumn('periode');
        });
    }
};
