<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const CATEGORIES = ['Cepat', 'Kilat', 'Standar', 'Komplit'];
    public const UNITS = ['kg', 'pcs', 'pasang'];

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'laundry_item_id');
    }
}
