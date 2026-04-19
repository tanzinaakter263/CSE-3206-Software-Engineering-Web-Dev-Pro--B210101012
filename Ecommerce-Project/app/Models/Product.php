<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;


    protected $appends = ['image_url'];

        public function getImageUrlAttribute() {
    
    if (empty($this->image)) {
        return "https://placehold.co/50x50?text=No+Image"; 
    }

    $path = public_path('uploads/products/small/' . $this->image);

    
    if (file_exists($path)) {
        return asset('uploads/products/small/' . $this->image);
    } else {
    
        return "https://placehold.co/50x50?text=File+Not+Found";
    }
}

function product_images(){
    return $this->hasMany(ProductImage::class);
}

function product_sizes(){
    return $this->hasMany(ProductSize::class);
}
}
