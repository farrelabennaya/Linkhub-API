<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = ['user_id', 'username', 'display_name', 'bio', 'avatar_url', 'theme'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;

        // kalau sudah ada user, sync name
        if ($this->relationLoaded('user') ? $this->user : $this->user()->exists()) {
            optional($this->user)->forceFill(['name' => $value])->saveQuietly();
        }
    }
}
