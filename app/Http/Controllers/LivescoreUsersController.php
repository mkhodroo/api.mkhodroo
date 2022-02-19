<?php

namespace App\Http\Controllers;

use App\Models\LivescoreUsersModel;
use Illuminate\Http\Request;

class LivescoreUsersController extends Controller
{
    private $model; 

    public function __construct() {
        $this->model = new LivescoreUsersModel();
    }

    public function get_user_by_domain($domain)
    {
        $user = $this->model->where('domain', $domain)->first();
        if($user)
            return $user;
        $now = date('Y-m-d H:i:s');
        $this->model->insert([
            'domain' => $domain,
            'expire_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        return $this->model->where('domain', $domain)->first();
    }

    public function edit_expire($user, $expire_at)
    {
        $user->expire_at = $expire_at;
        $user->save();
    }
}
