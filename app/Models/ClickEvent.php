<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClickEvent extends Model
{
    protected $fillable = ['link_id', 'ua', 'ip_hash', 'referer'];
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
