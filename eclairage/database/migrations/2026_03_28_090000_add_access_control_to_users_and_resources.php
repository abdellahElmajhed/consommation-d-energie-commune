<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->after('email');
            $table->string('status')->default('pending')->after('role');
            $table->string('access_type')->nullable()->after('status');
        });

        Schema::table('compteurs', function (Blueprint $table) {
            $table->string('type')->default('eclairage')->after('address');
        });

        Schema::table('consommation', function (Blueprint $table) {
            $table->string('type')->default('eclairage')->after('numero_contrat');
        });
    }

    public function down(): void
    {
        Schema::table('consommation', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('compteurs', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status', 'access_type']);
        });
    }
};
