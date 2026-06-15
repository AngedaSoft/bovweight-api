<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // 2 MB es suficiente para un avatar; las apps lo recortan en cliente.
            'avatar' => ['required', 'file', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }

    public function messages(): array
    {
        return [
            'avatar.required' => 'Debe adjuntar un archivo de imagen en el campo "avatar".',
            'avatar.image' => 'El archivo debe ser una imagen.',
            'avatar.max' => 'El avatar no puede pesar mas de 2 MB.',
            'avatar.mimes' => 'Formatos soportados: jpg, jpeg, png, webp.',
        ];
    }
}
