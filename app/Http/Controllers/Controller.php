<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExcelTable;

class Controller
{
    protected array $tables;

    public function __construct() {

        $table1 = new ExcelTable();
        
        $this->tables = [$table1];
    }

    public function tabloIstegi(Request $request) {
        $sortParam = $request->input('sortparam', 'id');
        $sortDir = $request->input('sortdir', 'ASC');
        $pageNum = $request->input('pagenum', 1) - 1;

        $query = $this->tables[$pageNum]->newQuery();

        $tableData = $query->orderBy($sortParam, $sortDir)->get();

        return response()->json($tableData);
    }
    
    /*
    public function ogrenciEkle(Request $request) {
        $validated = $request->validate([
            'studentName' => 'required|string',
            'studentLastName' => 'required|string',
            'studentNum' => 'required|string|unique:ogrenci,NO',
            'studentMajor' => 'required|string',
            'studentAge' => 'required|integer',
        ]);

        Student::create([
            'AD' => $validated['studentName'],
            'SOYAD' => $validated['studentLastName'],
            'NO' => $validated['studentNum'],
            'BOLUM' => $validated['studentMajor'],
            'YAS' => $validated['studentAge'],
        ]);

        return response()->json(['status' => 'success', 'message' => 'Başarıyla eklendi.']);
    }*/

    
    public function cellEdit(Request $request) {
        $request->validate([
            'rowindex' => 'required|numeric',
            'column'   => 'required|string',
            'data'     => 'nullable|string'
        ]);

        $rowindex = $request->input('rowindex')+1;
        $column = $request->input('column');
        $data = $request->input('data', ' ');
        $pageNum = $request->input('pagenum', 1) - 1;

        $this->tables[$pageNum]->newQuery()
                ->where('id', $rowindex)
                ->update([$column => $data]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cell updated successfully.'
        ]);
    }
}