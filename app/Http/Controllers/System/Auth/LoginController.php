<?php

namespace App\Http\Controllers\System\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    protected $redirectTo = '/system';

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
        if(Auth::guard('system')->check()){
            return redirect('/system');
        }
        return view('system.auth.login');
    }

    /**
     * 中间件
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('system');
    }

    /**
     * 登录
     * @param Request $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        if (md5($request->safety) != env('SAFETY')) {
            return false;
        }
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * 登出
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        return $this->succeed('退出成功');
    }
}
