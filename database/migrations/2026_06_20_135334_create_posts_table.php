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
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
    $table->string('title');
    $table->string('slug')->nullable();
    $table->string('thumbnail')->nullable();
    $table->text('content');
    $table->integer('views')->default(0);
    $table->boolean('status')->default(1);
    $table->timestamps();
});    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
