<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'name', 'url', 'average_rating',
        'total_ratings', 'total_reviews', 'last_synced_at'
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
