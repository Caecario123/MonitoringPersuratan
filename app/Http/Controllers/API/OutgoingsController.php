<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Letters;
use App\Models\Outgoingletter;
use App\Models\Filebalas;

use App\Models\User;
use File as files;

class OutgoingsController extends Controller
{
    public function balasan(Request $request,$id){
        $data = Letters::find($id);
        // dd($data);  
        return view('balasan',compact('data'));

    }public function editbalasan(Request $request,$id){
        $data = Outgoingletter::find($id);
        
        // dd($data);  
        return view('editbalasan',compact('data'));

    }
    public function update(Request $request, $id)
    {
    try {
        // Validasi permintaan
        $validator = Validator::make($request->all(), [
            'reference_number2' => 'required|string',
            'outgoing_letter_date' => 'required|date',
            'note' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'statusCode' => 400, 'message' => $validator->errors()->first()], 400);
        }

        // Set default values for fields if they are null
        $data['reference_number2'] = $request->reference_number2;
        $data['outgoing_letter_date'] = $request->outgoing_letter_date;
        $data['note'] = $request->note;

        // Simpan juga informasi file ke tabel file
        if ($request->file('file')) {
            // Store the data into the database
            $letter = Outgoingletter::whereId($id)->first();
            if (!$letter) {
                return response()->json(['status' => false, 'statusCode' => 404, 'error' => 'Letter not found.'], 404);
            }

            //hapus file lama
            $filePathlama = Filebalas::where('letter_balas_id', $id)->first()->path;
            $pdfPathlama = 'app/public/' . $filePathlama;
            if (file_exists(storage_path($pdfPathlama))) {
                unlink(storage_path($pdfPathlama));
            }

            $pdfName = time() . '_' . $request->file('file')->getClientOriginalName();
            // Simpan file PDF di penyimpanan 'pdfs' dalam penyimpanan 'public'
            $pdfPath = $request->file('file')->storeAs('Keluar', $pdfName, 'public');
            // Simpan informasi file ke dalam tabel file
            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_balas_id' => $id, // Ambil nomor referensi dari input
            ];
            Filebalas::where('letter_balas_id', $id)->update($fileData);

            // Update outgoing letter data
            Outgoingletter::whereId($id)->update($data);
        } else {
            return response()->json(['status' => false, 'statusCode' => 400, 'error' => 'No PDF uploaded.'], 400);
        }

        return response()->json(['status' => true, 'statusCode' => 200, 'message' => 'Data successfully updated.'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'statusCode' => 500, 'error' => $e->getMessage()], 500);
    }
    }

    public function store(Request $request)
    {
        try {
            // Validasi permintaan
            $validator = $request->validate([
                'reference_number2' => 'required|string',
                'outgoing_letter_date' => 'required|date',
                'letter_id' => 'nullable|string',
                'note' => 'nullable|string',
                'user_id' => 'nullable|string',
                'status' => 'nullable|string',
                'file' => 'required|mimes:pdf|max:2048',
            ]);
    
            // Ambil data dari permintaan
            $data = $request->all();
    
            // Simpan status dari data untuk pembaruan tabel Letters
            $status = $data['status'];
            $letterid = $data['letter_id'];
            $data['user_id'] = $data['user_id']; // $request->input('user_id', auth()->user()->id);
    
            Letters::whereId($letterid)->update(['status' => $status]);
    
            unset($data['status']);
    
            if ($request->file('file')) {
                // Simpan data ke dalam tabel outgoingletter
                $letter = Outgoingletter::create($data);
                $pdfName = time() . '_' . $request->file('file')->getClientOriginalName();
                // Simpan file PDF di penyimpanan 'pdfs' dalam penyimpanan 'public'
                $pdfPath = $request->file('file')->storeAs('Keluar', $pdfName, 'public');
                // Simpan informasi file ke dalam tabel file
                $fileData = [
                    'name' => $pdfName,
                    'path' => $pdfPath,
                    'letter_balas_id' => $letter->id, // Ambil nomor referensi dari input
                ];
                Filebalas::create($fileData);
            } else {
                return response()->json(['status' => false, 'statusCode' => 400, 'message' => 'No PDF uploaded.'], 400);
            }
    
            return response()->json(['status' => true, 'statusCode' => 200, 'message' => 'Data successfully stored.'], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'statusCode' => 400, 'error' => $e->getMessage()], 500);
        }
    }
    

    public function daftarbalasan($id = null)
    {  
        try {
            if ($id === null) {
                $outgoingLetters = Outgoingletter::all();
            } else {
                $outgoingLetters = Outgoingletter::where('letter_id', $id)->get();
            }
            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'data' => $outgoingLetters,
                'message' => 'Data Outgoing Letters retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'statusCode' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function streamOutgoingPDF($id)
    {

    $path = Filebalas::where('letter_balas_id', $id)->value('path');
  
    $pdfPath = storage_path('app/public/' . $path);

    $headers = [
        'Content-Type' => 'application/pdf',
    ];
    return response()->file($pdfPath, $headers);
   
    }


    public function delete($id)
    {
    try {
        // Mencari surat dengan ID yang diberikan
        $outgoingLetter = Outgoingletter::find($id);

        // Memeriksa apakah surat tersebut ditemukan
        if ($outgoingLetter) {
            // Menghapus file balasan jika ada
            $filebalas = Filebalas::where('letter_balas_id', $id)->get();
            if ($filebalas->isNotEmpty()) {
                foreach ($filebalas as $fb) {
                    $filePath = $fb->path;
                    $pdfPath2 = 'app/public/' . $filePath;
                    unlink(storage_path($pdfPath2));
                    $fb->delete();
                }
            }

            // Menghapus surat
            $outgoingLetter->delete();

            // Mengirim response sukses dengan status code 200
            return response()->json(['status' => true, 'statusCode' => 200, 'message' => 'Surat keluar berhasil dihapus'], 200);
        } else {
            // Jika surat tidak ditemukan, kirim response error dengan status code 404
            return response()->json(['status' => false, 'statusCode' => 400, 'message' => 'Letter not found'], 404);
        }
    } catch (\Exception $e) {
        // Jika terjadi kesalahan, kirim response error dengan status code 500
        return response()->json(['status' => false, 'statusCode' => 500, 'message' => $e->getMessage()], 500);
    }
    }


}
