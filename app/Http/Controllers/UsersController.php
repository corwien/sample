<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;

class UsersController extends Controller
{

    // 初始化中间件
    public function __construct()
    {
        $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy']
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

        return view('users.show', compact('user'));
    }

    //保存数据
    public function store(Request $request)
    {

      $this->validate($request, [
          'name' => 'required|max:50',
          'email' => 'required|email|unique:users|max:255',
          'password' => 'required'
      ]);

      $user = User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => $request->password,
      ]);

      // 提示信息
      session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');

      return redirect()->route('users.show', [$user]);

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


}
