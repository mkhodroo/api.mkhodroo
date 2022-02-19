<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveScoreModel extends Model
{
    use HasFactory;
    protected $table = 'livescores';
    protected $fillable = [
        'host', 'host_goals', 'guest', 'guest_goals', 'match_time', 'match_timer'
    ];
}
