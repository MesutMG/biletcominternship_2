<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheet extends Model {
    protected $fillable = ['file_id', 'sheet_index', 'col_count'];
    
    public function rows() {
        return $this->hasMany(SheetRow::class);
    }
}