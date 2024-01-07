<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'slug', 'url', 'primary_hex', 'is_visable', 'description'
    ];
    public function brands(): HasMany
    {
        return $this->hasmany(Product::class);
    }
}