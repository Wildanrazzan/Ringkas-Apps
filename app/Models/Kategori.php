<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    //
    protected $table = 'kategori';
    protected $fillable = ['NAME', 'kind', 'icon', 'color'];
    public $timestamps = false;

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'category_id');
    }
}
