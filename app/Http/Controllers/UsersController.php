<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Mail;

class UsersController extends Controller
{

    // 初始化中间件
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy', 'followings', 'followers']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 获取用户列表
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        // $users = User::all();
        $users = User::paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        $statuses = $user->statuses()
                         ->orderBy('created_at', 'desc')
                         ->paginate(30);
        return view('users.show', compact('user', 'statuses'));

        // return view('users.show', compact('user'));
    }

    //保存数据
    public function store(Request $request)
    {

      $this->validate($request, [
          'name' => 'required|max:50',
          'email' => 'required|email|unique:users|max:255',
          'password' => 'required|confirmed'   // 两次输入的密码需要验证
      ]);

      $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => $request->password,
      ]);

        // Auth::login($user);
        $this->sendEmailConfirmationTo($user);

      // 提示信息
      // session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
      // return redirect()->route('users.show', [$user]);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');

    }

    /**
     * 编辑页面
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        // 授权策略判断
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));

    }


    /**
     * 更新操作
     * @param $id
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'confirmed|min:6'
        ]);

        $user = User::findOrFail($id);

        // 授权策略判断
        $this->authorize('update', $user);

        $data = array_filter([
            'name' => $request->name,
            'password' => $request->password,
        ]);

        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $id);

    }


    /**
     * 删除
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('destroy', $user);

        // dd($user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();

    }


    /**
     * 发送确认邮件
     * @param $user
     */
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = '407544577@qq.com';
        $name = 'Corwien';
        $to = $user->email;

        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function($message) use ($from, $name, $to, $subject)
        {
            $message->from($from, $name)->to($to)->subject($subject);
        });

    }

    /**
     * 邮件确认激活账号
     * @param $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);

    }

    /**
     * 获取关注的人
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followings($id)
    {
        // 获取用户信息
        $user = User::findOrFail($id);
        $users = $user->followings()->paginate(30);

        $title = "关注的人";
        return view('users.show_follow', compact('users', 'title'));
    }

    /**
     * 获取粉丝
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followers($id)
    {
        $user  = User::findOrFail($id);
        $users = $user->followers()->paginate(30);

        $title = "粉丝";
        return view('users.show_follow', compact('users', 'title'));


    }


}
