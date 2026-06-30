<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $sortParam = $request->input('sortparam', 'ID');
        $sortDir = $request->input('sortdir', 'ASC');
        $perPage = $request->input('requestedcount', 10);

        $students = Student::query()
            ->when($request->filled('filterId'), fn($q) => $q->where('ID', 'LIKE', "%{$request->filterId}%"))
            ->when($request->filled('filterName'), fn($q) => $q->where('AD', 'LIKE', "%{$request->filterName}%"))
            ->when($request->filled('filterLastName'), fn($q) => $q->where('SOYAD', 'LIKE', "%{$request->filterLastName}%"))
            ->when($request->filled('filterNum'), fn($q) => $q->where('NO', 'LIKE', "%{$request->filterNum}%"))
            ->when($request->filled('filterMaj'), fn($q) => $q->where('BOLUM', 'LIKE', "%{$request->filterMaj}%"))
            ->when($request->filled('filterAge'), fn($q) => $q->where('YAS', 'LIKE', "%{$request->filterAge}%"))
            ->orderBy($sortParam, $sortDir)
            ->paginate($perPage);

        return response()->json($students);
    }

    public function store(Request $request)
    {
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
    }

    public function update(Request $request, $id)
    {
        $student = Student::where('NO', $id)->firstOrFail();

        $student->update([
            'AD' => $request->input('editName'),
            'SOYAD' => $request->input('editLastName'),
            'BOLUM' => $request->input('editMaj'),
            'YAS' => $request->input('editAge'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Başarıyla düzenlendi.']);
    }

    public function destroy($id)
    {
        $student = Student::where('NO', $id)->firstOrFail();
        $student->delete();

        return response()->json(['status' => 'success', 'message' => 'Başarıyla silindi.']);
    }
}