<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|in:COURANT,EPARGNE,MINEUR',
            'guardian_id' => 'required_if:type,MINEUR|exists:users,id',
            'initial_balance' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'guardian_id.required_if' => 'Un tuteur est obligatoire pour un compte mineur.',
        ];
    }
}