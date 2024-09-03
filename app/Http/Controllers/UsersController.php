<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
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
        $statuses = $user->statuses()
                         ->orderBy('created_at', 'desc')
                         ->paginate(10);
        return view('users.show', compact('user', 'statuses'));
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

        // 在用户注册成功后能够自动登录,但在邮箱注册这个环节，不能注册后自动登录，要用户查看收件箱，找到验证邮件后，点击验证才能登录
        // Auth::login($user);
        $this->sendEmailConfirmationTo($user);
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

    public function index(){
        // 在上面代码我们使用 paginate 方法来指定每页生成的数据数量为 6 条
        $users = User::paginate(6);
        return view('users.index', compact('users'));
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
        // 在查询不到指定用户时将返回一个 404 响应
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
