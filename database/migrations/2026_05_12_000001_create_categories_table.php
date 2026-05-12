<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id()->comment('ID категории');
            $table->string('name')->comment('Название категории');
            $table->text('description')->nullable()->comment('Текстовое описание категории');
            $table->timestamp('created_at')->nullable()->comment('Дата и время создания записи');
            $table->timestamp('updated_at')->nullable()->comment('Дата и время последнего обновления записи');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
