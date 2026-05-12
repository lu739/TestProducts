<?php

namespace App\Http\Requests;

use App\Data\IndexData;
use Illuminate\Foundation\Http\FormRequest;

class BaseIndexRequest extends FormRequest
{
    public const int DEFAULT_PAGE = 1;

    public const int DEFAULT_PER_PAGE = 15;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:10', 'max:50'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function toIndexData(): IndexData
    {
        return new IndexData(
            page: $this->integer('page', self::DEFAULT_PAGE),
            per_page: $this->integer('per_page', self::DEFAULT_PER_PAGE),
        );
    }
}
