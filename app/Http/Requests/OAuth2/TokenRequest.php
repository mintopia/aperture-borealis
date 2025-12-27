<?php

namespace App\Http\Requests\OAuth2;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TokenRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'grant_type' => [
                'required',
                Rule::in('urn:ietf:params:oauth:grant-type:device_code'),
            ],
            'device_code' => [
                'required',
                'string',
                Rule::exists('device_codes', 'device_code')->where(function (Builder $query) {
                    $query->where('client_id', Auth::user()->id);
                }),
            ]
        ];
    }
}
