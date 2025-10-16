<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dompet extends Model
{
    //
    protected $table = 'dompet';
    protected $fillable = ['user_id', 'name', 'type', 'currency', 'initial_balance', 'is_active'];
    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'dompet_id');
    }
}
