<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExcelTable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class Controller
{
    protected array $tables = [];

    public function __construct() {
        $allTables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $keyName = "Tables_in_{$dbName}";

        $sheetNames = [];
        foreach ($allTables as $table) {
            $tableName = $table->$keyName;
            if (preg_match('/^sheet\d+$/i', $tableName)) {
                $sheetNames[] = $tableName;
            }
        }

        natcasesort($sheetNames);

        foreach ($sheetNames as $sheetName) {
            $model = new ExcelTable();
            $model->setTable($sheetName);
            $this->tables[] = $model;
        }

        // Fallback: If no sheet tables exist yet, initialize sheet1
        if (empty($this->tables)) {
            $model = new ExcelTable();
            $model->setTable('sheet1');
            $this->tables[] = $model;
        }
    }

    public function tabloIstegi(Request $request) {
        $sortParam = $request->input('sortparam', 'id');
        $sortDir   = $request->input('sortdir', 'ASC');
        $pageNum   = (int) $request->input('pagenum', 1) - 1;

        if (!isset($this->tables[$pageNum])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Sheet at index {$pageNum} does not exist."
            ], 404);
        }

        $model = $this->tables[$pageNum];
        $tableName = $model->getTable();

        $tableData = $model->newQuery()->orderBy($sortParam, $sortDir)->get();

        $rowCount = $tableData->count();
        $columns = Schema::getColumnListing($tableName);
        $colCount = count(array_filter($columns, fn($col) => preg_match('/^C\d+$/i', $col)));
        $pageCount = count($this->tables);

        return response()->json([
            'data'      => $tableData,
            'rowcount'  => $rowCount,
            'colcount'  => $colCount,
            'pagecount' => $pageCount
        ]);
    }
    
    public function cellEdit(Request $request) {
        $request->validate([
            'rowindex' => 'required|numeric',
            'colindex' => 'required|numeric',
            'data'     => 'nullable|string'
        ]);

        $idNumber  = $request->input('rowindex') + 1;
        $colNumber = $request->input('colindex') + 1;
        $data      = $request->input('data', ' ');
        $pageNum   = (int) $request->input('pagenum', 1) - 1;

        if (!isset($this->tables[$pageNum])) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $column = sprintf("C%u", $colNumber);

        $this->tables[$pageNum]->newQuery()
            ->where('id', $idNumber)
            ->update([$column => $data]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Cell updated successfully.'
        ]);
    }

    public function addColumn(Request $request) {
        $pageNum   = (int) $request->input('pagenum', 1) - 1;

        if (!isset($this->tables[$pageNum])) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $tableName = $this->tables[$pageNum]->getTable();
        $columns   = Schema::getColumnListing($tableName);

        $colcount = array_filter($columns, fn($col) => preg_match('/^C\d+$/i', $col));
        $column   = sprintf("C%u", (count($colcount) + 1));

        Schema::table($tableName, function (Blueprint $table) use ($column) {
            $table->string($column)->nullable();
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Added column {$column} successfully."
        ]);
    }

    public function addRow(Request $request) {
        $pageNum   = (int) $request->input('pagenum', 1) - 1;

        if (!isset($this->tables[$pageNum])) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $model     = $this->tables[$pageNum];
        $tableName = $model->getTable();

        $columns  = Schema::getColumnListing($tableName);
        $colcount = array_values(array_filter($columns, fn($col) => preg_match('/^C\d+$/i', $col)));

        $newRowData = [];
        for ($i = 0; $i < count($colcount); $i++) {
            $columnName = $colcount[$i];
            $newRowData[$columnName] = ' ';
        }

        $model->newQuery()->create($newRowData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Added row successfully.'
        ]);
    }
    
    public function addPage(Request $request) {
        if (!$request->input('pagenum')) {
            return response()->json(['status' => 'error', 'message' => 'Page number required'], 400);
        }

        $pageNum      = (int) $request->input('pagenum');
        $newTableName = "sheet" . $pageNum;

        DB::statement("CREATE TABLE {$newTableName} LIKE sheet1");

        $columns  = Schema::getColumnListing($newTableName);
        $colcount = array_values(array_filter($columns, fn($col) => preg_match('/^C\d+$/i', $col)));

        $newRowData = [];
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < count($colcount); $j++) {
                $columnName = $colcount[$j];
                $newRowData[$columnName] = ' ';
            }
            DB::table($newTableName)->insert($newRowData);
        }

        // Dynamically add new table instance to runtime array
        $newModel = new ExcelTable();
        $newModel->setTable($newTableName);
        $this->tables[] = $newModel;

        return response()->json([
            'status'    => 'success',
            'message'   => "Page {$newTableName} created successfully with blank rows.",
            'pagecount' => count($this->tables)
        ]);
    }
}