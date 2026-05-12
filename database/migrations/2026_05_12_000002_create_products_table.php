<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id()->comment('ID товара');
            $table->string('name')->comment('Название товара');
            $table->text('description')->nullable()->comment('Текстовое описание товара');
            $table->decimal('price', 10, 2)->comment('Цена товара в денежных единицах');
            $table->foreignId('category_id')
                ->comment('ID категории, к которой относится товар')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('created_at')->nullable()->comment('Дата и время создания записи');
            $table->timestamp('updated_at')->nullable()->comment('Дата и время последнего обновления записи');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
