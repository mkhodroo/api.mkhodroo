<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivescoreCredits extends Model
{
    use HasFactory;
    protected $table = 'livescore_user_credit';

    public function user()
    {
        return LivescoreUsersModel::find($this->user_id);
    }

    
}
