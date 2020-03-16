<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{

    protected $fillable = ['firstname','lastname','email','image_file','phonenumber'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
