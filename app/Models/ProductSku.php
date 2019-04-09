<?php

namespace App\Models;

use App\Exceptions\ApiException;
use Illuminate\Database\Eloquent\Model;

class ProductSku extends Model
{
    protected $fillable = ['product_id', 'title', 'description', 'price', 'stock'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    //减库存
    public function decreaseStock($amount)
    {
        if ($amount < 0) {
            throw new ApiException('减库存不可小于0',400);
        }
        return $this->newQuery()->where('id', $this->id)->where('stock', '>=', $amount)->decrement('stock', $amount);
    }

    //加库存
    public function addStock($amount)
    {

        if ($amount < 0) {
            throw new ApiException('加库存不可小于0',400);
        }
        $this->increment('stock', $amount);
    }


}
