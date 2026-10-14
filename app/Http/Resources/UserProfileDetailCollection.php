<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Resources\UserAddressCollection;
use App\Models\UserAddress;
use App\Models\OrderDetail;
class UserProfileDetailCollection extends ResourceCollection
{
     public function toArray($request)
    {
         $data= isset($this->collection[0]) ? $this->collection[0]: [];
         if (!$data) {
                    return [];
                }
                return [
                 'id' => (int) $data->id,
                'author' => $this->user($data->created_by),
                'name' => $data->name,
                'username' => $data->username,
                'email' => $data->email,
                'email_verified_at' => $data->email_verified_at,
                'phone' => $data->phone,
                'gender' => $data->gender,
                'status' => $data->status,
                'avatar' => $data->avatar ? asset('storage/' . $data->avatar) : null,
                'roles' => $data->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'role_name' => $role->name,
                        ];
                    }),
                'user_type' => $data->user_type,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,
                // 'user_address' => $this->user_address($data->id),
                ];

    }
    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
    protected function user($user_id){
       $res=array();
       $role= User::find($user_id);
       if(!empty($role)){
           $res['id']=$role->id;
           $res['name']=$role->name; 
          
       }      
        return $res;
    }
    

}
