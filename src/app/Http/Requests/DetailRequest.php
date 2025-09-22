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
            // 'clock_in' => ['required', ''],
            // 'clock_out' => ['required', ''],
            // 'break1_start' => ['nullable', ''],
            // 'break1_end' => ['', ''],
            // 'break2_start' => ['', ''],
            // 'break2_end' => ['', ''],
            // 'note' => ['required', ''],

            
            //ステータス（勤務中とかの表示）と曜日が抜けている、追加するか決めたらモデルも修正する。
        ];
    }
}
