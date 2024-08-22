<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        // 使用 Auth 中间件提供的 guest 选项，用于指定一些只允许未登录用户访问的动作，因此我们需要通过对 guest 属性进行设置，只让未登录用户访问登录页面和注册页面。
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
        // 如果验证通过，validate() 方法会返回经过验证的输入数据，通常是一个关联数组。
        // 如果验证失败，Laravel会自动重定向用户回到之前的页面，并附带验证错误信息。

       $credentials = $this->validate($request, [
           'email' => 'required|email|max:255',
           'password' => 'required'
       ]);
    //. 如果匹配后两个值完全一致，会创建一个『会话』给通过认证的用户。会话在创建的同时，也会种下一个名为 laravel_session 的 HTTP Cookie，以此 Cookie 来记录用户登录状态，最终返回 true
       if (Auth::attempt($credentials, $request->has('remember'))) {
            // 登录成功后的相关操作
            session()->flash('success', '欢迎回来！');
            // Auth::user() 方法来获取 当前登录用户 的信息，
            $fallback = route('users.show', Auth::user());
            // 该方法可将页面重定向到上一次请求尝试访问的页面上，并接收一个默认跳转地址参数，当上一次请求记录为空时，跳转到默认地址上。
            return redirect()->intended($fallback);
        } else {
            // 登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            // 使用 withInput() 后，模板里 old('email') 将能获取到上一次用户提交的内容，这样用户就无需再次输入邮箱等内容
            return redirect()->back()->withInput();
        }



       return;
    }


    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }


}
