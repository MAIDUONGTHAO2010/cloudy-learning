<?php

namespace App\Models;

use App\Models\Concerns\HasLocalUrl;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasLocalUrl;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'order',
        'is_active',
        'parent_id',
    ];

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => static::localGetUrl($value),
            set: fn(?string $value) => static::localSetValue($value),
        );
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
