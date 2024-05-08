<?php

namespace App\Http\Controllers;
use App\Models\Filebalas;
use App\Models\Letters;
use App\Models\OutgoingLetter;
use App\Models\User;
use File as files;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OutgoingController extends Controller
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
            $letter = OutgoingLetter::whereId($id)->first();
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
            OutgoingLetter::whereId($id)->update($data);
        } else {
            return response()->json(['status' => false, 'statusCode' => 400, 'error' => 'No PDF uploaded.'], 400);
        }

        return response()->json(['status' => true, 'statusCode' => 200, 'message' => 'Data successfully updated.'], 200);
    } catch (\Exception $e) {
        return response()->json(['status' => false, 'statusCode' => 500, 'error' => $e->getMessage()], 500);
    }
    }

    public function store(Request $request,$id)
    {
        
        try {
            // Validasi permintaan
            $validator = $request->validate([
                'reference_number2' => 'required|string',
                'outgoing_letter_date' => 'required|date',
                'note' => 'nullable|string',
                'user_id' => 'nullable|string',
                'letter_id' => 'nullable|string',
                'status' => 'nullable|string',
                'file' => 'required|mimes:pdf|max:2048',
            ]);
    
            // Ambil data dari permintaan
            $data = $request->all();
    
            // Simpan status dari data untuk pembaruan tabel Letters
            $status = $data['status'];
            $letterid = $id;
            $data['user_id'] = $data['user_id']; // $request->input('user_id', auth()->user()->id);
            $data['letter_id'] = $letterid;
            
            Letters::whereId($id)->update(['status' => $status]);
    
            unset($data['status']);
    
            if ($request->file('file')) {
                // Simpan data ke dalam tabel outgoingletter
                $letter = OutgoingLetter::create($data);
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
            $outgoingLetters = OutgoingLetter::all();
            $filteredFiles = Filebalas::all();
            if ($outgoingLetters->isEmpty()) { 
                return response()->json([
                    'status' => true,
                    'statusCode' => 200,
                    'data' => [
                        'replyletter' => [],
                        'filebalas' => []
                    ],
                    'message' => 'No outgoing letters found'
                ], 200);
            }
            } else {
                $outgoingLetters = OutgoingLetter::where('letter_id', $id)->get();
                $filteredFiles = [];
                foreach ($outgoingLetters as $letter) {
                    $filebalas = Filebalas::where('letter_balas_id',$letter->id)->get();
                    $filteredFiles[] = $filebalas; 
                    // foreach ($filebalas as $file) {
                    //     if ($file->id == $letter->id) {
                    //         dd($outgoingLetters);
                            
                    //        // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    //     }    
                    // }
                }
            if ($outgoingLetters->isEmpty()) { // Memeriksa jika tidak ada data untuk ID tertentu
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'No outgoing letters found for provided ID'
                ], 404);
            }
        }
        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'data' => [
                'replyletter' => $outgoingLetters,
                'filebalas' => $filteredFiles
            ],
            'message' => 'Data Outgoing Letters retrieved successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function detailbalasan($id = null)
{  
    try {
        if ($id === null) {
            $outgoingLetters = OutgoingLetter::all();
            if ($outgoingLetters->isEmpty()) { // Memeriksa jika tidak ada data
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'No outgoing letters found'
                ], 404);
            }
        } else {
            $outgoingLetters = OutgoingLetter::where('id', $id)->get();
            $filebalas = Filebalas::where('letter_balas_id',$id)->get();
            if ($outgoingLetters->isEmpty()) { // Memeriksa jika tidak ada data untuk ID tertentu
                return response()->json([
                    'status' => false,
                    'statusCode' => 404,
                    'message' => 'No outgoing letters found for provided ID'
                ], 404);
            }
        }
        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'data' => [
                'replyletter' => $outgoingLetters,
                'filebalas' => $filebalas
            ],
            'message' => 'Data Outgoing Letters retrieved successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'statusCode' => 500,
            'message' => 'Internal Server Error',
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
        $outgoingLetter = OutgoingLetter::find($id);

        // Memeriksa apakah surat tersebut ditemukan
        if ($outgoingLetter) {
            // Menghapus file balasan jika ada
            $filebalas = Filebalas::where('letter_balas_id', $id)->get();
            if ($filebalas->isNotEmpty()) {
                foreach ($filebalas as $fb) {
                    $filePath = $fb->path;
                    $pdfPath2 = 'app/public/' . $filePath;
            
                    // Mendapatkan path lengkap ke file
                    $fullPath = storage_path($pdfPath2);
            
                    // Periksa apakah file tersebut ada sebelum mencoba menghapus
                    if (file_exists($fullPath)) {
                        unlink($fullPath); // Menghapus file jika ada
                        $fb->delete(); // Menghapus referensi dari database
                    } else {
                        Log::warning('File not found: ' . $fullPath);
                        // Anda bisa menambahkan kode lain di sini untuk menangani kasus ketika file tidak ditemukan
                    }
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
