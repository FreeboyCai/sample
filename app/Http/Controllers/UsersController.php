<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers;
use Auth;
use Mail;

class UsersController extends Controller
{
    
    public function __construct(){

        $this->middleware('auth',['except'=>['show','create','store','index','confirmEmail']]);
         $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    //
    public function create(){
    	return view('users.create');
    }

    public function show(User $user){
        $this->authorize('update',$user);
    	return view('users.show',compact('user'));
    }


    
    public function store(Request $request)
    {
    	$this->validate($request,[
    	'name' => 'required|max:50',
    	'email' => 'required|email|unique:users|max:255',
    	'password' => 'required|confirmed|min:6'
    ]);
    /*return;*/
     	
     	$user = User::create([

     		'name' => $request->name,
     		'email' => $request->email,
     		'password'=>bcrypt($request->password),
     	]);
       
        $this->sendEmailConfirmationTo($user);
     	session()->flash('success','验证邮件已经发送！');
     	return redirect('/');
    }


    protected function sendEmailConfirmationTo($user){
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });       
        }


    public function edit(User $user){

        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }


    public  function update(User $user,Request $request){
        $this->validate($request,[
            'name' => 'required|max:50',
            'password'=>'required|confirmed|min:6',
        ]);
        $this->authorize('update',$user);

        $user->update([
            'name' => $request->name,
            'password' => $request->password,
        ]);

        session()->flash('success','用户资料修改成功！');
        return redirect()->route('users.show',$user->id);
    }


    public function index(){

        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

     public function destroy(User $user)
    {
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    public function confirmEmail($token){

        $user = User::where('activation_token',$token)->firstOrFail();
        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
