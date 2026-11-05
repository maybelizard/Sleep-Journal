<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SleepEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;
        $entry = $this->route('sleepEntry');

        return [
            'date' => [
                'required',
                'date',
                Rule::unique('sleep_entries')
                    ->where('user_id', $userId)
                    ->ignore($entry?->id),
            ],
            'bedtime' => ['required', 'date'],
            'wake_time' => ['required', 'date', 'after:bedtime'],
            'quality' => ['required', 'integer', 'between:1,5'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'habits' => ['nullable', 'array'],
            'habits.*' => [
                'integer',
                Rule::exists('habits', 'id')->where('user_id', $userId),
            ],
        ];
    }
}
