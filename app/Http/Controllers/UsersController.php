<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);


    }

    // 注册表单
    public function create(){
        return view('users.create');
    }
    // 展示某个用户的个人信息
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    //接受用户输入的表单数据，创建用户
    public function store(Request $request)
    {
        // confirmed是为了验证密码正确
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        // 如果验证失败，在laravel的机制下，会自动重定向回 create() 方法渲染的页面
        // 由于你已经在create()方法渲染的模板代码写了 错误信息提醒的代码。所以再次渲染时，会展示错误信息
        // 只要在模板代码写$errors，这个变量因为laravel机制，定义为错误信息
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        //在用户注册成功后能够自动登录
        Auth::login($user);

        // flash会在下一次请求中显示，之后会自动删除。
        // 这个时候就感觉请求不再是 用户对服务器的请求，服务器内也会对别的控制器发送请求，然后执行某些函数
        // 所以说是发出请求，我感觉就变成了 调用某个控制器的方法
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user->id]);
    }


    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
    }
}
