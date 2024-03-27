<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    //
    public function index(){
        return view('auth.login');
    }
    
    public function login_proses(Request $request){
        $input = $request->all();
        $data = [
            'email' => $request->email,
            'password'  =>$request->password
        ];
        if(Auth::attempt($data)){
            $user = Auth::user();
            if ($user->type == 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->type == 'kakan') {
                return redirect()->route('kakan.dashboard');
            } elseif ($user->type == 'tatausaha') {
                return redirect()->route('tatausaha.dashboard');
            } elseif ($user->type == 'seksi1') {
                return redirect()->route('seksi1.dashboard');
            } elseif ($user->type == 'seksi2') {
                return redirect()->route('seksi2.dashboard');
            } elseif ($user->type == 'seksi3') {
                return redirect()->route('seksi3.dashboard');
            } elseif ($user->type == 'seksi4') {
                return redirect()->route('seksi4.dashboard');
            } elseif ($user->type == 'seksi5') {
                return redirect()->route('seksi5.dashboard');
            } else {
                return redirect()->route('dashboard');
            }
        } else {
            return redirect()->route('login')->with('failed','Email atau Password Salah');
        }
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login')->with('success','Kamu berhasil logout');
    }
}
