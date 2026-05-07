<?php

namespace App\Http\Requests\OAuth2;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'scope' => [
                'required',
                'string',
                Rule::exists('social_providers', 'code')
                    ->where(function (Builder $query) {
                        $query->whereEnabled(true);
                    }),
            ],
        ];
    }
}
