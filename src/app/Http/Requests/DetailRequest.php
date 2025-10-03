<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i','before:clock_out'],
            
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            
            'break1_start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before:break1_end'],
            
            'break1_end' => ['nullable', 'date_format:H:i', 'after:break1_start', 'before:clock_out'],
            
            'break2_start' => ['nullable', 'date_format:H:i', 'after:break1_end', 'before:break2_end'],
            
            'break2_end' => ['nullable', 'date_format:H:i', 'after:break2_start', 'before_or_equal:clock_out'],
            
            'note' => ['required','string','max:255'],
        ];
    }

    public function messages() {
        return [
        'clock_in.required' => '出勤時間を入力してください',
        'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
        'clock_out.required' => '退勤時間を入力してください',
        'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
        'break1_start.after' => '休憩時間が不適切な値です',
        'break1_start.before' => '休憩時間が不適切な値です',
        'break1_end.after' => '休憩時間が不適切な値です',
        'break1_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        'break2_start.after' => '休憩時間が不適切な値です',
        'break2_start.before' => '休憩時間が不適切な値です',
        'break2_end.after' => '休憩時間が不適切な値です',
        'break2_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        'note.required' => '備考を記入してください',
        ];
    }
}
