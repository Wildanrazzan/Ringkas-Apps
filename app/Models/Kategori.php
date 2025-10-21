<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    //
    protected $table = 'kategori';
    protected $fillable = ['name', 'kind', 'icon', 'color','user_id'];
    public $timestamps = false;

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
