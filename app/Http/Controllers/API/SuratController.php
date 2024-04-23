<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Letters;
use App\Models\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
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

        try {
            $data = $request->all();
            $data['read_status'] = $request->input('read_status', '0');
            $data['disposition_note'] = $request->input('disposition_note', '-');
            $data['disposition_process'] = $request->input('disposition_process', 'Belum ditindak lanjuti');
            $data['status'] = $request->input('status', 'Pending');
            $data['user_id'] = $request->input('user_id', Auth::id());
            $letter = Letters::create($data);

            $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
            $pdfPath = $request->file('file')->storeAs('Masuk', $pdfName, 'public');

            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_id' => $letter->id,
            ];

            File::create($fileData);

            return response()->json(['message' => 'Letter created successfully', 'letter' => $letter, 'file' => $fileData], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create letter', 'error' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reference_number' => 'nullable',
            'letters_type' => 'required',
            'letter_date' => 'required|date',
            'received_date' => 'nullable|date',
            'from' => 'nullable|string',
            'description' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->only(['reference_number', 'letters_type', 'letter_date', 'received_date', 'from', 'description']);

        try {
            // Update the letter information
            $letter = Letters::where('id', $id)->update($data);

            // Handle the file update
            $existingFile = File::where('letter_id', $id)->first();
            if ($existingFile) {
                // Delete the old file
                Storage::disk('public')->delete($existingFile->path);
            }

            // Store the new file
            $pdfName = time() . '_' . $request->file('file')->getClientOriginalName();
            $pdfPath = $request->file('file')->storeAs('Masuk', $pdfName, 'public');

            // Update file information or create new file record
            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_id' => $id,
            ];
            File::updateOrCreate(['letter_id' => $id], $fileData);

            return response()->json(['message' => 'Letter updated successfully', 'letter' => Letters::find($id), 'file' => $fileData], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update letter', 'error' => $e->getMessage()], 500);
        }
    }
    public function delete($id)
    {
        // Mencari surat dengan ID yang diberikan
        $letter = Letters::find($id);

        // Memeriksa apakah surat tersebut ditemukan
        if (!$letter) {
            return response()->json(['message' => 'Surat tidak ditemukan.'], 404);
        }

        // Mencari dan menghapus file terkait dengan surat
        $files = File::where('letter_id', $id)->get();
        if ($files->isNotEmpty()) {
            foreach ($files as $file) {
                if ($file->path && Storage::disk('public')->exists($file->path)) {
                    Storage::disk('public')->delete($file->path);
                }
                $file->delete();
            }
        }

        // Mencari dan menghapus outgoing letters terkait dengan surat
        $outgoingLetters = Outgoingletter::where('letter_id', $id)->get();
        if ($outgoingLetters->isNotEmpty()) {
            foreach ($outgoingLetters as $outgoingLetter) {
                $outgoingLetter->delete();
            }
        }

        // Menghapus record surat
        $letter->delete();

        // Mengirim response sukses
        return response()->json(['message' => 'Surat berhasil dihapus.'], 200);
    }
    public function disposisikan(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'disposition_date' => 'required|date',
            'disposition_note' => 'nullable|string',
            'disposition_process' => 'required|string',
            'disposition_process2' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Menggabungkan proses disposisi jika ada input kedua
        $process1 = $request->disposition_process;
        $process2 = $request->disposition_process2;
        $combinedProcess = trim($process1 . ' ' . $process2);

        // Menyiapkan data untuk diupdate
        $data = [
            'disposition_date' => $request->input('disposition_date', Carbon::now()->toDateString()),
            'disposition_note' => $request->input('disposition_note'),
            'disposition_process' => $combinedProcess
        ];

        // Melakukan update pada database
        $updateStatus = Letters::whereId($id)->update($data);

        // Memeriksa apakah update berhasil
        if ($updateStatus) {
            return response()->json(['message' => 'Disposisi berhasil diperbarui'], 200);
        } else {
            return response()->json(['message' => 'Gagal memperbarui disposisi, surat tidak ditemukan'], 404);
        }
    }

}