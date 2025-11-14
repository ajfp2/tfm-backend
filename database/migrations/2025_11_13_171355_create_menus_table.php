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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('label', 100);
            $table->string('icon', 50)->nullable();
            $table->string('route', 150)->nullable();
            $table->integer('order')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->json('roles')->nullable(); // [1, 2] = roles permitidos
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
