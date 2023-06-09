<?php

namespace App\Http\Requests;

use App\Enums\ArchiveTypes;
use App\Enums\GenderTypes;
use App\Enums\ReligionTypes;
use App\Rules\grecaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AddArchiveRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'nik' => 'required|regex:/^[0-9]{16}$/',
            'nisn' => 'required|regex:/^[0-9]{10}$/',
            'name' => 'required|min:3',
            'mother_name' => 'required|min:3',
            'father_name' => 'required|min:3',
            'birthday'    => 'required|regex:/^([a-zA-Z\s-]+)(\s+)?,\s+([0-9]+)\s+([a-zA-Z]+)\s+([0-9]{4})$/',
            'school'      => 'required',
            'graduated_year' => 'required|integer',
            'phone'          => ['required', 'regex:/^(08[0-9]{9,10})$/'],
            'email'          => 'required|email',
            'type'           => [
                'required',
                new Enum(ArchiveTypes::class),
            ],
            'religion' => [
                'required',
                new Enum(ReligionTypes::class),
            ],
            'gender' => [
                'required',
                new Enum(GenderTypes::class),
            ],
            'address' => [
                'required',
            ],
            'religion' => [
                'required',
                new Enum(ReligionTypes::class),
            ],
            '_gtoken' => [
                'required',
                new grecaptcha,
            ]
        ];
    }
}
