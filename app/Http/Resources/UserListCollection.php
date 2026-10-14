<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\Permission\Models\Role;

class UserListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<int|string, mixed>
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id' => (int) $data->id,
                    'name' => $data->name,
                    'email' => $data->email,
                    'email_verified' => !empty($data->email_verified_at),
                    'phone' => $data->phone,
                    'gender' => $data->gender,
                    'status' => $data->status,
                    'avatar' => $data->avatar ? asset('storage/' . $data->avatar) : null,
                    'user_type' => $data->user_type,
                    'created_at' => $data->created_at,
                    'updated_at' => $data->updated_at,
                    'roles' => $data->roles->map(function ($role) {
                        return [
                            'id' => $role->id,
                            'role_name' => $role->name,
                        ];
                    }),
                    'detail_links' => [
                        'single_user' => route('users.show', $data->id),
                    ],
                ];
            }),
        ];
    }

    /**
     * Additional metadata for the response.
     */
    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200,
        ];
    }
}
