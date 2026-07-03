<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelTable extends Model
{
    protected $table = 'excel';

    protected $primaryKey = 'id';
    
    public $timestamps = false;

    protected $fillable = [
        'C1', 
        'C2', 
        'C3', 
        'C4', 
        'C5', 
        'C6', 
        'C7', 
        'C8', 
        'C9', 
        'C10'
    ];
}