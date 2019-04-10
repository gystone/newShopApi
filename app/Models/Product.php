<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'description', 'image_id', 'on_sale',
        'rating', 'sold_count', 'review_count', 'price'
    ];

    protected $casts = [
        'on_sale' => 'boolean', // on_sale 是一个布尔类型的字段
    ];


    // 与商品SKU关联
    public function skus()
    {
        return $this->hasMany(ProductSku::class);
    }




    public function image()
    {
        return $this->belongsTo(Image::class);
    }

}