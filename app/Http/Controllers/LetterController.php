<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Letter;
use App\Models\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LetterController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_number' => 'nullable|unique:letters',
            'letters_type' => 'required',
            'letter_date' => 'required|date',
            'received_date' => 'nullable|date',
            'from' => 'nullable|string',
            'description' => 'nullable|string',
            'disposition_date' => 'nullable|date',
            'disposition_note' => 'nullable|string',
            'disposition_process' => 'nullable|string',
            'status' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'read_status' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $data['read_status'] = $request->input('read_status', '0');
        $data['disposition_note'] = $request->input('disposition_note', '-');
        $data['disposition_process'] = $request->input('disposition_process', 'Belum ditindak lanjuti');
        $data['status'] = $request->input('status', 'Pending');
        $data['user_id'] = $request->input('user_id', Auth::id());

        $letter = Letter::create($data);

        $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
        $pdfPath = $request->file('file')->storeAs('Masuk', $pdfName, 'public');

        $fileData = [
            'name' => $pdfName,
            'path' => $pdfPath,
            'letter_id' => $letter->id,
        ];

        File::create($fileData);

        return response()->json(['message' => 'Letter created successfully', 'letter' => $letter, 'file' => $fileData], 201);
    }
}