<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelTable extends Model
{
    protected $table = 'sheet1';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = []; 
}