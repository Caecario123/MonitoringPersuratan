<?php

namespace App\Http\Controllers;
use App\Models\File;
use App\Models\Filebalas;
use App\Models\Letters;

use App\Models\OutgoingLetter;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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

            return response()->json([
                'message' => 'Letter created successfully',
                'status' => true,
                'statusCode' => 201,
                'data' => [
                    'letter' => $letter,
                    'file' => $fileData
                ]
            ], 201);
            
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Failed to create letter',
                        'status' => false,
                        'statusCode' => 500,
                        'error' => $e->getMessage()
                    ], 500);}
    }

    public function update(Request $request, $id)
    {
    try {
        // Validasi permintaan
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
            return response()->json(['status' => false, 'statusCode' => 400, 'message' => $validator->errors()->first()], 400);
        }

        $data = $request->only(['reference_number', 'letters_type', 'letter_date', 'received_date', 'from', 'description']);

        if ($request->file('file')) {
            // Update the letter information
            $letter = Letters::whereId($id)->first();
            if (!$letter) {
                return response()->json(['status' => false, 'statusCode' => 404, 'error' => 'Letter not found.'], 404);
            }
            // Periksa apakah surat berhasil diperbarui
            
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
            Letters::whereId($id)->update($data);

            return response()->json([
                'message' => 'Letter updated successfully',
                'status' => true,
                'statusCode' => 200,
                'data' => [
                    'letter' => Letters::find($id),
                    'file' => $fileData
                ]
            ], 200);
            
        }
        } catch (\Exception $e) {
            // Jika terjadi kesalahan saat memperbarui surat
            return response()->json([
                'message' => 'Failed to update letter',
                'status' => false,
                'statusCode' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function delete($id)
        {
        // Mencari surat dengan ID yang diberikan
        $letter = Letters::find($id);

        // Memeriksa apakah surat tersebut ditemukan
        if (!$letter) {
            return response()->json(['message' => 'Surat tidak ditemukan.', 'status' => false, 'statusCode' => 404], 404);
        }

        try {
            // Mencari dan menghapus file terkait dengan surat
        
            // Mencari dan menghapus outgoing letters terkait dengan surat
            $outgoingLetters = Outgoingletter::where('letter_id', $id)->get();
            if ($outgoingLetters->isNotEmpty()) {
                foreach ($outgoingLetters as $outgoingLetter) {
                    
                    $idbalas=$outgoingLetter->id;
                    $filebalas = Filebalas::where('letter_balas_id', $idbalas)->get();
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
                    $outgoingLetter->delete();
                }
                
            }
            $files = File::where('letter_id', $id)->get();
            if ($files->isNotEmpty()) {
                foreach ($files as $file) {
                    if ($file->path && Storage::disk('public')->exists($file->path)) {
                        Storage::disk('public')->delete($file->path);
                    }
                    $file->delete();
                }
            }

            // Menghapus record surat
            $letter->delete();

            // Mengirim response sukses
            return response()->json(['message' => 'Surat berhasil dihapus.', 'status' => true, 'statusCode' => 200], 200);
        } catch (\Exception $e) {
            // Jika terjadi kesalahan saat menghapus surat
            return response()->json(['message' => 'Gagal menghapus surat.', 'error' => $e->getMessage(), 'status' => false, 'statusCode' => 500], 500);
        }
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
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
                'status' => false,
                'statusCode' => 400
            ], 400);
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
            return response()->json([
                'message' => 'Disposisi berhasil diperbarui',
                'status' => true,
                'statusCode' => 200
            ], 200);
        } else {
            return response()->json([
                'message' => 'Gagal memperbarui disposisi, surat tidak ditemukan',
                'status' => false,
                'statusCode' => 404
            ], 404);
        }
    }

    public function show()
{
    try {
        $letters = Letters::all(); // Mengambil semua data surat
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
    } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
    public function showtu()
    {
        try {

        $letters = Letters::where('disposition_process', 'like', '%Tata Usaha%')->get(); // Mengambil semua data surat
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Tata Usaha')->get();
// Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);    
        }
        }
    public function showseksi1()
    {   try{
        $letters = Letters::where('disposition_process', 'like', '%Seksi penetapan hak dan pendaftaran%')->get();
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Seksi penetapan hak dan pendaftaran')->get();
// Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);    }
    }
    public function showseksi2()
    {   try{
        $letters = Letters::where('disposition_process', 'like', '%Seksi survei dan pemetaan%')->get();
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Seksi survei dan pemetaan')->get();
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);}    }
    public function showseksi3()
    {   try{
        $letters = Letters::where('disposition_process', 'like', '%Seksi penataan dan pemberdayaan%')->get();
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Seksi penataan dan pemberdayaan')->get();
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);}    }
    public function showseksi4()
    {   try{
        $letters = Letters::where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')->get();
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')->get();
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);    }}
    public function showseksi5()
    {   try{
        $letters = Letters::where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%')->get();
        $files = File::all(); // Mengambil semua data file

        // Menggabungkan data surat dan data file menjadi satu koleksi
        $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Seksi pengendalian dan penanganan sengketa')->get();
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters,
                'file' => $files
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);    }}
        
        public function showdetailletter($id)
    {
    try {
        $letter = Letters::find($id); // Mengambil data surat berdasarkan ID
        // $file = File::find($id); // Mengambil data file berdasarkan ID
        $file = File::where('letter_id', $id)->get(); // Langsung mengambil nilai kolom 'type'

        if (!$letter && !$file) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data not found'
            ], 404);
        }

        // Mengembalikan data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letter,
                'file' => $file
            ]
        ], 200);
    } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function showAllLetters()
    {
    try {
        $letter = Letters::all(); // Mengambil data surat berdasarkan ID
        $file = File::all(); // Mengambil data file berdasarkan ID
        $outgoingletter = OutgoingLetter::all(); // Mengambil data surat berdasarkan ID
        $filebalas = Filebalas::all();
        if (!$letter && !$file) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data not found'
            ], 404);
        }
        if (!$outgoingletter && !$filebalas) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data not found'
            ], 404);
        }
        // Mengembalikan data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letter,
                'file' => $file,
                'outgoingletter' =>$outgoingletter,
                'filebalas' => $filebalas
            ]
        ], 200);
    } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function showletter()
    {   try{
         // Mengambil semua data file
        $id = Auth::id();
        $type = User::where('id', $id)->value('type'); // Langsung mengambil nilai kolom 'type'
        if ($type=='admin'){
            $letters = Letters::all();
            // $letters = Letters::where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%')->get();
            // dd($letter_id);
            $files = File::all();
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        if ($type=='tatausaha'){
            $letters = Letters::where('disposition_process', 'like', '%Tata Usaha%')->get(); // Mengambil semua data surat
            $files = File::all(); // Mengambil semua data file
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        if ($type=='seksi1'){
            $letters = Letters::where('disposition_process', 'like', '%Seksi penetapan hak dan pendaftaran%')->get();
            $files = File::all(); // Mengambil semua data file
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        if ($type=='seksi2'){
            $letters = Letters::where('disposition_process', 'like', '%Seksi survei dan pemetaan%')->get();
            $files = File::all();
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
            dd($letters);
        };
        if ($type=='seksi3'){
            $letters = Letters::where('disposition_process', 'like', '%Seksi penataan dan pemberdayaan%')->get();
            $files = File::all();
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        if ($type=='seksi4'){
            $letters = Letters::where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')->get();
            $files = File::all();
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        if ($type=='seksi5'){
            $letters = Letters::where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%')->get();
            $files = File::all();
            $filteredFiles = [];
            foreach ($letters as $letter) {
                foreach ($files as $file) {
                    if ($file->letter_id == $letter->id) {
                        $filteredFiles[] = $file; // Menambahkan file ke array jika letter_id cocok dengan id dari letters
                    }
                }
            }
        };
        // Menggabungkan data surat dan data file menjadi satu koleksi
        // $datas = $letters->merge($files);
        // $datas = Letters::where('disposition_process', 'Seksi pengendalian dan penanganan sengketa')->get();
        // Mengembalikan koleksi data dalam format JSON dengan status 200 (OK)
        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'user' => $type,
            'data' => [
                'letter' => $letters,
                'file' => $filteredFiles
            ]
        ], 200);
        } catch (\Exception $e) {
        // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);    }}
        
        public function deleteAllFiles()
{
    try {
        // Menghapus semua data di tabel File
        File::truncate();

        // Menghapus semua data di tabel Filebalas
        Filebalas::truncate();

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'All files deleted successfully'
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'statusCode' => 500,
            'message' => 'Failed to delete files',
            'error' => $e->getMessage()
        ], 500);
    }
}
}