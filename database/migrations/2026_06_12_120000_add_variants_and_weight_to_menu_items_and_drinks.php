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
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->text('description')->nullable()->change();
            $table->decimal('price', 10, 2)->nullable()->change();
            $table->string('weight')->nullable()->after('description');
            $table->json('price_variants')->nullable()->after('price');
        });

        Schema::table('drinks', function (Blueprint $table): void {
            $table->string('weight')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            $table->dropColumn(['weight', 'price_variants']);
            $table->text('description')->nullable(false)->change();
            $table->decimal('price', 10, 2)->nullable(false)->change();
        });

        Schema::table('drinks', function (Blueprint $table): void {
            $table->dropColumn('weight');
        });
    }
};
