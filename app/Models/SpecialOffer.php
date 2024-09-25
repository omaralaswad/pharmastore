<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{

    protected $fillable = [
        'name',
        'description',
        'old_price',
        'new_price',
        'category_id',
        'supplier_id',
        'image',
    ];

    
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // العلاقة مع المورد (Supplier)
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
