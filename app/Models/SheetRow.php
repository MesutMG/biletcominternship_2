<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SheetRow extends Model {
    protected $fillable = ['sheet_id', 'row_index', 'data'];
    protected $casts = ['data' => 'array'];
}