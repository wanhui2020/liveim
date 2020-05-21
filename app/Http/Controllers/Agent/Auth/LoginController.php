<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/agent';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * 登录界面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        if (Auth::guard('agent')->check()) {
            return redirect('/agent');
        }
        return view('agent.auth.login');
    }

    /**
     * 中间件
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('agent');
    }

    protected function username()
    {
        return "user_name";
    }


    /**
     * 登出
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect('/agent/login');
    }
}
