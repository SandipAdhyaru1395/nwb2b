<?php

namespace App\Http\Controllers\apps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserViewSecurity extends Controller
{
  public function index($id)
  {
     $data['user'] = User::findOrFail($id);
     $data['roles'] = Role::where('id','!=',1)->get();

    return view('content.apps.app-user-view-security',$data);
  }
}
