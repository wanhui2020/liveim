<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class CustomerRequest extends FormRequest
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
        switch (Request::getPathInfo()) {
            case '/api/auth/login':
                return [
                    'phone' => 'required|numeric',
                    'password' => 'required|min:6|max:12',
                ];
                break;
            case '/api/auth/register':
                return [
                    'phone' => 'required|numeric|unique:customer_users',
                    'password' => 'required|min:6|max:12',
                    'invite_code' => 'required|numeric',
                    'code' => 'required|numeric',
                ];
                break;
            case '/api/auth/forget':
                return [
                    'phone' => 'required|numeric',
                    'code' => 'required|numeric',
                    'password' => 'required|min:6|max:12',
                ];
                break;
            default:
                return [];
        }
    }

    public function messages()
    {
        return [
            'phone.required' => '手机号不能为空',
            'phone.unique' => '手机号已注册',
            'phone.numeric' => '手机号格式不正确',
            'invite_code.numeric' => '邀请码必须为数字',
            'code.numeric' => '验证码必须为数字',
            'password.required' => '密码不能为空',
            'password.min' => '密码不能低于6位',
            'password.max' => '密码不能超过12位',
        ];
    }
}
