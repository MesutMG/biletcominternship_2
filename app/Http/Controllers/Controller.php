<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Sheet;
use App\Models\SheetRow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

//tabloIstegi -> getPage
//cellEdit -> editCell
    
class Controller {
    protected function getFile(string $fileName): ?File {
        return File::where('name', $fileName)->first();
    }

    protected function getSheet(int $fileId, int $pageNum): ?Sheet {
        return Sheet::where('file_id', $fileId)
                    ->where('sheet_index', $pageNum)
                    ->first();
    }

    protected function getSheetRow(int $sheetId, int $rowIndex): ?SheetRow {
        return SheetRow::where('sheet_id', $sheetId)
                    ->where('row_index', $rowIndex)
                    ->first();
    }

    protected function getAllSheetRows(int $sheetId) {
        return SheetRow::where('sheet_id', $sheetId)
                    ->orderBy('row_index', 'asc')
                    ->get();
    }

    /*--------------------------------------------------------------------------------------*/

    public function createFile(Request $request) {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $fileName = $request->input('filename');

        $newFile = File::create(['name' => $fileName]);

        $newSheet = Sheet::create([
            'file_id' => $newFile->id,
            'sheet_index' => 1,
            'col_count' => 10
        ]);

        for ($i=0; $i < 10; $i++) {
            $newSheetRow = SheetRow::create([
                'sheet_id' => $newSheet->id,
                'row_index' => $i,
                'data' => array_fill_keys(range(0, 9), "")
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'File added successfully.'
        ]);
    }

    public function deleteFile(Request $request) {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $file = $this->getFile($request->input('filename'));
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $file->delete();
        //cascading delete drops 'children sheets and their children sheet_rows' in mysql itself

        return response()->json([
            'status'  => 'success',
            'message' => 'File deleted successfully.'
        ]);
    }

    public function getFiles(Request $request) {
        $files = File::withCount('sheets')->get();

        return response()->json([
            'status' => 'success',
            'files'  => $files
        ]);
    }

    public function addPage(Request $request) {
        $request->validate([
            'filename' => 'required|string'
        ]);
        $fileName = $request->input('filename');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $newSheetIndex = $file->sheets()->count() + 1;

        $newSheet = Sheet::create([
            'file_id' => $file->id,
            'sheet_index' => $newSheetIndex,
            'col_count' => 10
        ]);

        for ($i=0; $i < 10; $i++) {
            $newSheetRow = SheetRow::create([
                'sheet_id' => $newSheet->id,
                'row_index' => $i,
                'data' => array_fill_keys(range(0, 9), "")
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Page created successfully.'
        ]);
    }

    public function deletePage(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum'  => 'required|numeric'
        ]);

        $pageNum = (int) $request->input('pagenum');

        $file = $this->getFile($request->input('filename'));
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheet->delete();

        Sheet::where('file_id', $file->id)
             ->where('sheet_index', '>', $pageNum)
             ->decrement('sheet_index');

        return response()->json([
            'status'  => 'success',
            'message' => 'Page deleted successfully.'
        ]);
    }

    public function getPage(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum   = (int) $request->input('pagenum', 1);

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheetRows = $this->getAllSheetRows($sheet->id);
        //each $sheetRow->data is automatically cast to a PHP array
        //$casts = ['data' => 'array'] in SheetRow.php
        
        $gridData = $sheetRows->pluck('data');

        return response()->json([
            'data'      => $gridData,
            'rowcount'  => $sheetRows->count(),
            'colcount'  => $sheet->col_count,
            'pagecount' => $file->sheets()->count()
        ]);
    }

    public function addRow(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $nextRowIndex = $sheet->rows()->count();
        $newRowData = array_fill_keys(range(0, $sheet->col_count - 1), "");

        SheetRow::create([
            'sheet_id'  => $sheet->id,
            'row_index' => $nextRowIndex,
            'data'      => $newRowData
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Added row successfully.'
        ]);
    }

    public function insertRow(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric',
            'targetrowindex' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');
        $targetRowIndex = (int) $request->input('targetrowindex');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        SheetRow::where('sheet_id', $sheet->id)
                ->where('row_index', '>=', $targetRowIndex)
                ->orderBy('row_index', 'desc')
                ->increment('row_index');

        SheetRow::create([
            'sheet_id'  => $sheet->id,
            'row_index' => $targetRowIndex,
            'data'      => array_fill_keys(range(0, $sheet->col_count - 1), "")
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Inserted row successfully.'
        ]);
    }

    public function deleteRow(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric',
            'rowindex' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');
        $rowIndex = (int) $request->input('rowindex');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheetRow = $this->getSheetRow($sheet->id, $rowIndex);
        if (!$sheetRow) return response()->json(['status' => 'error', 'message' => 'Requested row is not found' ], 404);

        $sheetRow->delete();

        SheetRow::where('sheet_id', $sheet->id)
                ->where('row_index', '>', $rowIndex)
                ->decrement('row_index');

        return response()->json([
            'status'  => 'success',
            'message' => 'Deleted row successfully.'
        ]);
    }

    public function addColumn(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $newColIndex = (string) $sheet->col_count;
        $sheet->increment('col_count');

        $sheetRows = $this->getAllSheetRows($sheet->id);

        foreach ($sheetRows as $sheetRow) {
            $rowData = $sheetRow->data;
            $rowData[$newColIndex] = "";
            $sheetRow->data = $rowData;
            $sheetRow->save();
        }

        return response()->json([
            'status'  => 'success',
            'message' => "Added column {$newColIndex} successfully."
        ]);
    }

    public function insertColumn(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric',
            'targetcolindex' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');
        $targetColIndex = (int) $request->input('targetcolindex');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheetRows = $this->getAllSheetRows($sheet->id);

        foreach ($sheetRows as $sheetRow) {
            $rowData = $sheetRow->data;
            array_splice($rowData, $targetColIndex, 0, "");
            $sheetRow->data = $rowData;
            $sheetRow->save();
        }

        $sheet->increment('col_count');

        return response()->json([
            'status'  => 'success',
            'message' => 'Inserted column successfully.'
        ]);
    }

    public function deleteColumn(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric',
            'colindex' => 'required|numeric'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');
        $colIndex = (int) $request->input('colindex');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheetRows = $this->getAllSheetRows($sheet->id);
        if (!$sheetRows) return response()->json(['status' => 'error', 'message' => 'Requested rows are not found' ], 404);

        foreach ($sheetRows as $sheetRow) {
            $rowData = $sheetRow->data;

            //remove the element starting at $colIndex and auto-shift the ones after
            array_splice($rowData, $colIndex, 1);

            $sheetRow->data = $rowData;
            $sheetRow->save();
        }

        $sheet->decrement('col_count');

        return response()->json([
            'status'  => 'success',
            'message' => 'Deleted column successfully.'
        ]);
    }

    public function editCell(Request $request) {
        $request->validate([
            'filename' => 'required|string',
            'pagenum' => 'required|numeric',
            'rowindex' => 'required|numeric',
            'colindex' => 'required|numeric',
            'data'     => 'nullable|string'
        ]);

        $fileName = $request->input('filename');
        $pageNum = (int) $request->input('pagenum');
        $rowIndex = (int) $request->input('rowindex');
        $colIndex = $request->input('colindex');
        $data = $request->input('data', '');

        $file = $this->getFile($fileName);
        if (!$file) return response()->json(['status' => 'error', 'message' => 'Requested file is not found'], 404);

        $sheet = $this->getSheet($file->id, $pageNum);
        if (!$sheet) return response()->json(['status' => 'error', 'message' => 'Requested sheet is not found'], 404);

        $sheetRow = $this->getSheetRow($sheet->id, $rowIndex);
        if (!$sheetRow) return response()->json(['status' => 'error', 'message' => 'Requested row is not found' ], 404);

        $rowData = $sheetRow->data;
        $rowData[$colIndex] = $data;
        $sheetRow->data = $rowData;
        $sheetRow->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Cell updated successfully.'
        ]);
    }
}