<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $guarded = [
        'private'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function save(array $options  = [])
    {
        $slug = Str::slug($this->title, '-');
        $isUnique = Post::where('slug', '=', $slug)->first();
        if (!empty($isUnique)) {
            $slug = Str::slug($this->title .'-'. Carbon::now()->subSeconds(1), '-');
        }
        $this->slug = $slug;
        return parent::save($options);
    }
}
