<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class RegisterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => 'bail|required|numeric|unique:customers',
            'password' => 'required|confirmed|min:6',
            'code' => 'required|numeric',
            'agent_id' => 'nullable',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => '手机号不能为空！',
            'phone.numeric' => '手机号必须为数字！',
            'phone.unique' => '手机号已注册，请换个手机号!',
            'password.required' => '密码不能为空！',
            'password.min' => '密码不能小于六位!',
            'password.confirmed' => '两次密码输入不一致',
            'code.required' => '验证码不能为空！',
            'code.numeric' => '验证码必须为数字',
        ];
    }

}
