<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'ogrenci';

    protected $primaryKey = 'ID';
    
    public $timestamps = false;

    protected $fillable = [
        'AD', 
        'SOYAD', 
        'NO', 
        'BOLUM', 
        'YAS'
    ];
}