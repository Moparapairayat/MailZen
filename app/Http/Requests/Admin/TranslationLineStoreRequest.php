<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationLineStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $localeId = $this->route('translation_locale')?->id;
        $group = (string) ($this->input('group') ?? '*');
        $group = trim($group) === '' ? '*' : trim($group);

        return [
            'group' => ['nullable', 'string', 'max:100'],
            'key' => [
                'required',
                'string',
                'max:255',
                Rule::unique('translation_lines', 'key')->where(fn ($q) => $q
                    ->where('translation_locale_id', $localeId)
                    ->where('group', $group)
                ),
            ],
            'text' => ['nullable', 'string'],
        ];
    }
}
