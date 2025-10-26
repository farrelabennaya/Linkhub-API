<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $fillable = ['user_id', 'title', 'url', 'is_active', 'position'];
    protected $casts = ['is_active' => 'boolean'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function clicks()
    {
        return $this->hasMany(ClickEvent::class);
    }
}
