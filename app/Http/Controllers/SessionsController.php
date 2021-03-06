<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 展示登录视图
    public function create()
    {
        return view('sessions.create');
    }

    // 存储数据
    public function store(Request $request)
    {
        // 输入参数验证
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        $credentials = [
          'email'    => $request->email,
          'password' => $request->password,
        ];

        // 账号密码验证
        if(Auth::attempt($credentials, $request->has('remember')))
        {
            // dd(Auth::user());

            // 判断注册的账号是否激活
            if(Auth::user()->activated)
            {
                // 登录成功后的操作
                session()->flash('success', '欢迎回来！');

                // return redirect()->route('users.show', [Auth::user()]);
                return redirect()->intended(route('users.show', [Auth::user()]));
            }
            else
            {
                Auth::logout();

                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        }
        else
        {

            // 登录失败后的操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back();
        }

    }


    /**
     * 退出登录
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy()
    {
        // 退出会话
        Auth::logout();

        // 消息提示
        session()->flash('success', '您已成功退出！');

        // 重定向
        return redirect('login');

    }


}
