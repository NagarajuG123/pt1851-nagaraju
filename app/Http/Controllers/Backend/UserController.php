<?php

namespace App\Http\Controllers\Backend;

use DB;

use App\Models\User\AdminProfile;
use App\Models\User;

use App\Http\Controllers\Controller;
use App\Transformers\UserTransformer;


use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{ 
    public function list(Request $request)
    {
        $limit = $request->query('limit') ?? 10;
        $roleId = $request->query('role_id');
        $searchQuery = $request->query('q'); 
        $users = User::select('user.id','user.user_name','user.role_id','user.image_path')
                    ->join('admin_profile', 'admin_profile.user_id', '=', 'user.id')
                    ->where('role_id', $roleId)
                    ->whereNull('user.deleted_at');
        if($searchQuery) {
            $users = $users->where(DB::raw("admin_profile.first_name"), 'like', '%' . $searchQuery . '%')
            ->orWhere(DB::raw("admin_profile.last_name"), 'like', '%' . $searchQuery . '%')
            ->orWhere(DB::raw("admin_profile.email"), 'like', '%' . $searchQuery . '%');
        }
        $users =  $users->paginate($limit);
        return response()->json([ 
            'hasMore' => $users->hasMorePages(),
            'data' =>   UserTransformer::collection($users) ?? null
            ]);
    }
    public function create(Request $request)
    {   
        $validator = Validator::make($request->json()->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => 'required|email|unique:admin_profile',
            'user_name' => 'required|min:3',
            'password' => ['required', 'regex:/(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8}\S+$/'],
            'confirm_password' => 'required|same:password',
            'role_id' => 'required',
        ]);
        if ($validator->passes($request)) {
            $user = $request->all();
            $user['created_by'] = Auth::user()->id;
            $user['updated_by'] = Auth::user()->id;
            $user['password'] = Hash::make($request->password);
            $user['password_hash'] = Hash::make($request->password);
            $user['slug'] = Str::slug($request->first_name) . '-' .  Str::slug($request->last_name);
            $user['image_path'] = $request->image_path ?? 'admin/default_profile_image.png';
            $model = User::create($user);
            if(!empty($model)) {
                $admin = AdminProfile::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'user_id' => $model->id
                ]);
            }
            return response()->json([
                "success" => true,
                "data" => [
                    "id" => $model->id,
                    "message" => "Admin has been created successfully"
                ]
            ]);
        } 
        else {
            return response()->json($validator->errors(), 400);
        }
    }
    public function update($id,Request $request)
    {   
        $validator = Validator::make($request->json()->all(), [
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => 'required|email|unique:admin_profile,email,'.$id,
            'user_name' => 'required|min:3',
            'password' => ['required', 'regex:/(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8}\S+$/'],
            'confirm_password' => 'required|same:password',
            'role_id' => 'required',
        ]);
        if ($validator->passes($request)) {
            $user = User::findOrFail($id);
            $user->update($request->all());
            $user->update(['password' => (Hash::make($request->password)),
                           'slug' => Str::slug($request->first_name) . '-' . Str::slug($request->last_name)]);
            $user->adminProfile->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email
            ]);
            return response()->json([
                "success" => true,
                "data" => [
                    "id" => $id,
                    "message" => "Admin has been updated successfully"
                ]
            ]);
        } 
        else {
            return response()->json($validator->errors(), 400);
        }   
    }
    public function delete($id)
    {
        $user = User::findOrFail($id);
        if(empty($user->deleted_at)){
            $user->update(['deleted_at' => (new \DateTime())->format('Y-m-d H:i:s')]);
            return response()->json([
            'success' => true,
            'message' =>"Admin has been deleted successfully"
            ]);
        }
        else{
            return response()->json([
                "success" => true,
                "message" => "Admin does not exist"
            ]);
        }
    }
}