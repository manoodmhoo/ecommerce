<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['sku', 'name', 'description', 'price', 'stock', 'image', 'category_id', 'image_url'];
    protected $hidden = ['image'];
    protected $appends = ['image_url'];

    public  function getImageUrlAttribute() {
        return asset('storage/upload').'/'.$this->image;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items');
    }


}
