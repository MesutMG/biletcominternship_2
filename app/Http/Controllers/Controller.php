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
        $dbName = DB::getDatabaseName();
        $allTables = DB::select('SHOW TABLES');
        $keyName = "Tables_in_{$dbName}";

        $sheetNames = [];
        foreach ($allTables as $table) {
            if (preg_match('/^sheet\d+$/i', $table->$keyName)) {
                $sheetNames[] = $table->$keyName;
            }
        }

        foreach ($sheetNames as $sheetName) {
            $this->tables[] = (new ExcelTable())->setTable($sheetName);
        }

        if (empty($this->tables)) {
            $this->tables[] = (new ExcelTable())->setTable('sheet1');
        }
    }

    private function getSheet(int $pageNum) {
        return $this->tables[$pageNum - 1] ?? null;
    }

    private function getColCount(string $tableName) {
        $columns = Schema::getColumnListing($tableName);
        return array_values(array_filter($columns, fn($col) => is_numeric($col)));
    }

    private function getRowCount(string $tableName) {
        return DB::table($tableName)->count();
    }

    public function tabloIstegi(Request $request) {
        $sortParam = $request->input('sortparam', 'id');
        $sortDir   = $request->input('sortdir', 'ASC');
        $pageNum   = (int) $request->input('pagenum', 1);

        $model = $this->getSheet($pageNum);
        if (!$model) {
            return response()->json(['status' => 'error', 'message' => "Sheet at index {$pageNum} does not exist."], 404);
        }

        $data = $model->newQuery()->orderBy($sortParam, $sortDir)->get();

        return response()->json([
            'data'      => $data,
            'rowcount'  => $data->count(),
            'colcount'  => count($this->getColCount($model->getTable())),
            'pagecount' => count($this->tables)
        ]);
    }
    
    public function cellEdit(Request $request) {
        $request->validate([
            'rowindex' => 'required|numeric', //starts from 0
            'colindex' => 'required|numeric', //starts from 0
            'data'     => 'nullable|string'
        ]);

        $pageNum = (int) $request->input('pagenum', 1);
        $model = $this->getSheet($pageNum);

        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $model->newQuery()
            ->where('id', $request->input('rowindex')+1)
            ->update([(int) $request->input('colindex') => $request->input('data', ' ')]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Cell updated successfully.'
        ]);
    }

    public function addColumn(Request $request) {
        $pageNum = (int) $request->input('pagenum', 1);
        $model = $this->getSheet($pageNum);

        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $tableName = $model->getTable();

        //columns start at 0, counting them gives the index for the next column
        $newColIndex = count($this->getColCount($tableName)); 

        Schema::table($tableName, function (Blueprint $table) use ($newColIndex) {
            $table->string((string) $newColIndex)->nullable();
        });

        return response()->json([
            'status'  => 'success',
            'message' => "Added column {$newColIndex} successfully."
        ]);
    }

    public function deleteColumn(Request $request) {
        $request->validate([
            'colindex' => 'required|numeric',
            'pagenum'  => 'required|numeric'
        ]);

        $colIndex = $request->input('colindex');
        $pageNum = (int) $request->input('pagenum', 1);

        $model = $this->getSheet($pageNum);
        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $tableName = $model->getTable();
        $cols = $this->getColCount($tableName);
        $totalCols = count($cols);

        if (!in_array((string)$colIndex,$cols, true)) {
            return response()->json(['status' => 'error', 'message' => 'Column does not exist'], 400);
        }

        DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$colIndex}`");

        for ($i = $colIndex + 1; $i < $totalCols; $i++) {
            $oldName = (string)$i;
            $newName = (string) ($i - 1);

            DB::statement("ALTER TABLE `{$tableName}` RENAME COLUMN `{$oldName}` TO `{$newName}`");
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Column {$colIndex} deleted and higher columns shifted."
        ]);
    }

    public function addRow(Request $request) {
        $pageNum = (int) $request->input('pagenum', 1);
        $model = $this->getSheet($pageNum);

        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $cols = $this->getColCount($model->getTable());
        $newRowData = array_fill_keys($cols, null); 

        $model->newQuery()->create($newRowData);

        return response()->json([
            'status'  => 'success',
            'message' => 'Added row successfully.'
        ]);
    }

    public function deleteRow(Request $request) {
        $request->validate([
            'rowindex' => 'required|numeric',
            'pagenum'  => 'required|numeric'
        ]);

        $rowIndex = (int) $request->input('rowindex');
        $pageNum  = (int) $request->input('pagenum', 1);
        
        $model = $this->getSheet($pageNum);
        if (!$model) {
            return response()->json(['status' => 'error', 'message' => 'Sheet not found'], 404);
        }

        $tableName = $model->getTable();

        //adjust + 1 since rowindex is 0 based on frontend
        $targetId = $rowIndex + 1; 

        DB::table($tableName)->where('id', $targetId)->delete();

        return response()->json([
            'status'  => 'success',
            'message' => "Row {$targetId} deleted successfully."
        ]);
    }
    
    public function addPage(Request $request) {
        $request->validate([
            'pagenum' => 'required|numeric'
        ]);

        $pageNum = (int) $request->input('pagenum');
        $newTableName = "sheet" . $pageNum;

        DB::statement("CREATE TABLE {$newTableName} LIKE sheet0");

        $cols = $this->getColCount($newTableName);
        $newRowData = array_fill_keys($cols, null);
        
        //generares 10 empty rows
        $insertData = array_fill(0, 10, $newRowData); 
        DB::table($newTableName)->insert($insertData);

        $this->tables[] = (new ExcelTable())->setTable($newTableName);

        return response()->json([
            'status'    => 'success',
            'message'   => "Page {$newTableName} created successfully with blank rows.",
            'pagecount' => count($this->tables)
        ]);
    }
}