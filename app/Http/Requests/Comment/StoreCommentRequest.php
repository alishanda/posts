<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
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
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['sometimes', 'nullable', 'exists:comments,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Содержимое комментария обязательно для заполнения.',
            'content.string' => 'Содержимое комментария должно быть строкой.',
            'content.max' => 'Содержимое комментария не должно превышать 1000 символов.',
            'parent_id.exists' => 'Родительский комментарий не найден.',
        ];
    }
}
