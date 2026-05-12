<?php

namespace App\Http\Requests;

use App\Data\IndexData;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends BaseIndexRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'filter.category_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
            ],
        ]);
    }
}
