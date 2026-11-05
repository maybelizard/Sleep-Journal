<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Models\SleepEntry;

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
                'before_or_equal:today',
                Rule::unique('sleep_entries')
                    ->where('user_id', $userId)
                    ->ignore($entry?->id),
            ],
            'bedtime' => ['required', 'date_format:H:i'],
            'wake_time' => ['required', 'date_format:H:i'],
            'quality' => ['required', 'integer', Rule::in(array_keys(SleepEntry::QUALITY_LABELS))],
            'notes' => ['nullable', 'string', 'max:1000'],
            'habits' => ['nullable', 'array'],
            'habits.*' => [
                'integer',
                Rule::exists('habits', 'id')->where('user_id', $userId),
            ],
        ];
    }

    public function entryData(): array
    {
        $data = $this->validated();
        unset($data['habits']);

        $wake = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['wake_time']);
        $bed = Carbon::createFromFormat('Y-m-d H:i', $data['date'].' '.$data['bedtime']);

        if ($bed->greaterThanOrEqualTo($wake)) {
            $bed->subDay();
        }

        $data['bedtime'] = $bed;
        $data['wake_time'] = $wake;

        return $data;
    }
}
