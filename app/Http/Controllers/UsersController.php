<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Controllers;
use Auth;

class UsersController extends Controller
{
    
    public function __construct(){

        $this->middleware('auth',['except'=>['show','create','store','index']]);
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
        Auth::login($user);
     	session()->flash('success','欢迎，您将在这里开启一段新的旅程');
     	return redirect()->route('users.show',[$user]);
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

}
