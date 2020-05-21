<?php

namespace App\Http\Requests;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Http\FormRequest;

class SystemUserRequest extends FormRequest
{

    public function authorize(){
        if($this->input('account')=='aaa@abc.com'){
            return true;
        }
        return true;
    }

    protected function failedAuthorization()
    {

        throw new AuthenticationException('该帐号已被拉黑');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ];
    }
    public function messages(){
        return [
            'name.required' => '姓名不能为空',
            'email.required'  => '邮箱不能为空',
            'password.required'  => '密码不能为空',
        ];
    }
}
