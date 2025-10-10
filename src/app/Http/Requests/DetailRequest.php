<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;


class DetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isDetailUpdate = $this->route() && $this->route()->getActionName() === 'App\Http\Controllers\UserController@updateDetail';


        return [
            'clock_in' => ['required', 'date_format:H:i'],
            
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            
            'break1_start' => ['nullable', 'date_format:H:i', 'after_or_equal:clock_in', 'before_or_equal:break1_end'],
            
            'break1_end' => ['nullable', 'date_format:H:i', 'after_or_equal:break1_start', 'before:clock_out'],
            
            'break2_start' => ['nullable', 'date_format:H:i', 'after_or_equal:break1_end', 'before_or_equal:break2_end'],
            
            'break2_end' => ['nullable', 'date_format:H:i', 'after_or_equal:break2_start', 'before:clock_out'],

            'note' => $isDetailUpdate ? ['required','string','max:255'] : ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages() {
        return [
        'clock_in.required' => '出勤時間を入力してください',
        'clock_out.required' => '退勤時間を入力してください',
        'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
        'break1_start.after_or_equal' => '休憩時間が不適切な値です',
        'break1_start.before_or_equal' => '休憩時間が不適切な値です',
        'break1_end.after_or_equal' => '休憩時間が不適切な値です',
        'break1_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        'break2_start.after_or_equal' => '休憩時間が不適切な値です',
        'break2_start.before_or_equal' => '休憩時間が不適切な値です',
        'break2_end.after_or_equal' => '休憩時間が不適切な値です',
        'break2_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
        'note.required' => '備考を記入してください',
        ];
    }
}
