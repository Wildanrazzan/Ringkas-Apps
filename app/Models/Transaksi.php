<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    //
    protected $table = 'transaksi';
    protected $fillable = ['dompet_id','category_id','trx_date','amount','note'];
    public $timestamps = true;

    public function dompet()
    {
        return $this->belongsToMany(Dompet::class, 'dompet_id');
    }

    public function kategori()
    {
        return $this->belongsToMany(Kategori::class, 'category_id');
    }
}
