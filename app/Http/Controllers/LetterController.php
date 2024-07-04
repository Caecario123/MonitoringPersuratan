<?php

namespace App\Http\Controllers;
use App\Models\File;
use App\Models\Filebalas;
use App\Models\Letters;

use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
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
            $data['status'] = $request->input('status', 'Baru');
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
    // Validate request
    $validator = Validator::make($request->all(), [
        'reference_number' => 'nullable',
        'letters_type' => 'nullable',
        'letter_date' => 'nullable|date',
        'received_date' => 'nullable|date',
        'from' => 'nullable|string',
        'description' => 'nullable|string',
        'file' => 'nullable|mimes:pdf|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'statusCode' => 400, 'message' => $validator->errors()->first()], 400);
    }

    try {
        $data = $request->only(['reference_number', 'letters_type', 'letter_date', 'received_date', 'from', 'description']);

        // Find the letter to update
        $letter = Letters::find($id);
        if (!$letter) {
            return response()->json(['status' => false, 'statusCode' => 404, 'message' => 'Letter not found.'], 404);
        }

        if ($request->file('file')) {
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

            // Add file data to the response
            $data['file'] = $fileData;
        }

        // Update the letter data
        $letter->update($data);

        return response()->json([
            'message' => 'Letter updated successfully',
            'status' => true,
            'statusCode' => 200,
            'data' => [
                'letter' => $letter,
                'file' => isset($fileData) ? $fileData : null
            ]
        ], 200);
    } catch (\Exception $e) {
        // If an error occurs while updating the letter
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
    
    if ($process1==='Belum ditindak lanjuti'&& $process2 ==='Belum ditindak lanjuti') {
        $combinedProcess =['Belum ditindak lanjuti'];
    } else {
        $combinedProcess = array_filter([$process1, $process2]);
    }
    // Ambil status surat
    $status = Letters::whereId($id)->value('status');

    // Periksa apakah proses dan status sudah benar
    if ($status != 'Selesai' && in_array('Belum ditindak lanjuti', $combinedProcess)) {
        $newStatus = 'Baru';
    } else {
        $newStatus = 'Proses';
    }
    

    // Menyiapkan data untuk diupdate
    $data = [
        'disposition_date' => $request->input('disposition_date', Carbon::now()->toDateString()),
        'disposition_note' => $request->input('disposition_note'),
        'disposition_process' => json_encode($combinedProcess),
        'status' => $newStatus
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
        $letters = Letters::all();

        // Mendecode disposition_process pada setiap data yang ada di letters
        $letters = $letters->map(function ($letter) {
            // Coba mendecode nilai disposition_process
            $decoded = json_decode($letter->disposition_process, true);
    
            // Cek apakah decoding berhasil atau tidak
            if (json_last_error() === JSON_ERROR_NONE) {
                $letter->disposition_process = $decoded;
            } else {
                // Jika decoding gagal, Anda bisa memutuskan untuk tetap menyimpan nilai asli
                // atau menggantinya dengan nilai default seperti array kosong
                $letter->disposition_process = [$letter->disposition_process];
            }
    
            return $letter;
        });        
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
        if ($letter) {
        // Coba mendecode nilai disposition_process
        $decoded = json_decode($letter->disposition_process, true);

        // Cek apakah decoding berhasil atau tidak
        if (json_last_error() === JSON_ERROR_NONE) {
            $letter->disposition_process = $decoded;
        } else {
            // Jika decoding gagal, Anda bisa memutuskan untuk tetap menyimpan nilai asli
            // atau menggantinya dengan nilai default seperti array kosong
            $letter->disposition_process = [$letter->disposition_process];
        }
    }
        // $file = File::find($id); // Mengambil data file berdasarkan ID
        $file = File::where('letter_id', $id)->get(); // Langsung mengambil nilai kolom 'type'

        if (!$letter && !$file) {
            return response()->json([
                'status' => 'error',
                'statusCode' => 404,
                'message' => 'Data not found'
            ], 404);
        }
    Letters::whereId($id)->update(['read_status' => '1']);


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
        $id = Auth::id();
        $type = User::where('id', $id)->value('type');
        $isi = "";
        $incoming = Letters::query();
        $outcome = OutgoingLetter::query();

        switch ($type) {
            case 'Kasubag. TU':
                $isi = "Tata Usaha";
                break;
            case 'Seksi Penetapan Hak & Pendaftaran':
                $isi = "Seksi penetapan hak dan pendaftaran";
                break;
            case 'Seksi Survei & Pemetaan':
                $isi = "Seksi survei dan pemetaan";
                break;
            case 'Seksi Penataan & Pemberdayaan':
                $isi = "Seksi penataan dan pemberdayaan";
                break;
            case 'Seksi Pengadaan Tanah & Pengembangan':
                $isi = "Seksi pengadaan tanah dan pengembangan";
                break;
            case 'Seksi Pengendalian & Penanganan Sengketa':
                $isi = "Seksi pengendalian dan penanganan sengketa";
                break;
            case 'Admin':
            case 'Kepala Kantor':
                $letters = Letters::all();
                $outgoingLetters = OutgoingLetter::all();
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'Unauthorized access'
                ], 403);
        }

        $combinedData = [];

        if ($type !== 'Admin' && $type !== 'Kepala Kantor') {
            $incoming->whereJsonContains('disposition_process', $isi);
            $outcome->where('from', 'like', "%$type%");     

            $letters = $incoming->get();
            $outgoingLetters = $outcome->get();
        }

        // Add letters to combined data
        foreach ($letters as $letter) {
            $combinedData[] = [
                'id' => $letter->id,
                'from' => $letter->from,
                'date' => $letter->letter_date,
                'description' => $letter->description,
                'type' => 'surat masuk',
            ];
        }

        // Add outgoing letters to combined data
        foreach ($outgoingLetters as $outgoingLetter) {
            $combinedData[] = [
                'id' => $outgoingLetter->id,
                'from' => $outgoingLetter->from,
                'date' => $outgoingLetter->outgoing_letter_date,
                'description' => $outgoingLetter->note,
                'type' => 'surat keluar',
            ];
        }

        // Check for filter parameters
        if (request()->has('kategori') || request()->has('tanggal')) {
            $kategori = request()->get('kategori', '');
            $tanggal = request()->get('tanggal', '');

            $combinedData = array_filter($combinedData, function($item) use ($kategori, $tanggal) {
                $matchesKategori = empty($kategori) || stripos($item['type'], $kategori) !== false;
                $matchesTanggal = empty($tanggal) || $item['date'] === $tanggal;

                return $matchesKategori && $matchesTanggal;
            });

            // Re-index the array to prevent pagination issues
            $combinedData = array_values($combinedData);
        }

        // Paginate the combined data
        $currentPage = request()->has('page') ? request()->get('page') : 1;
        $perPage = 10; // You can adjust this as needed
        $paginationResult = $this->customPagination(collect($combinedData), $currentPage, $perPage);

        // Return the JSON response with the paginated data
        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => ['letter' => $paginationResult['data']],
            'pagination' => $paginationResult['pagination'],
        ], 200);
    } catch (\Exception $e) {
        // Return error message if something goes wrong
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}



public function showletter(Request $request)
{
    try {
        $currentPage = $request->input('page', 1);
        $searchQuery = $request->input('typing', ''); // Get the search query from the request
        $id = Auth::id();
        $type = User::where('id', $id)->value('type');

        // Define the base query
        $query = Letters::query();

        // Apply filters based on user type
        if ($type == 'Kasubag. TU') {
            $query->where('disposition_process', 'like', '%Tata Usaha%');
        } elseif ($type == 'Seksi Penetapan Hak & Pendaftaran') {
            $query->where('disposition_process', 'like', '%Seksi penetapan hak dan pendaftaran%');
        } elseif ($type == 'Seksi Survei & Pemetaan') {
            $query->where('disposition_process', 'like', '%Seksi survei dan pemetaan%');
        } elseif ($type == 'Seksi Penataan & Pemberdayaan') {
            $query->where('disposition_process', 'like', '%Seksi penataan dan pemberdayaan%');
        } elseif ($type == 'Seksi Pengadaan Tanah & Pengembangan') {
            $query->where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%');
        } elseif ($type == 'Seksi Pengendalian & Penanganan Sengketa') {
            $query->where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%');
        } elseif ($type == 'Admin' || $type == 'Kepala Kantor') {
            // No additional filters needed
        } else {
            // Handle case for unknown types or restricted access
            return response()->json([
                'status' => 'error',
                'statusCode' => 403,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Apply search query if provided
        if (!empty($searchQuery)) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('from', 'like', '%' . $searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $searchQuery . '%')
                  ->orWhere('disposition_process', 'like', '%' . $searchQuery . '%');
            });
        }

        // Sort by the most recent letters
        $query->orderBy('created_at', 'desc');

        // Get the filtered letters
        $letters = $query->get();
        $deadlineLetters = $letters->filter(function ($letter) {
            return (strtotime($letter->disposition_date) < strtotime('-3 days')) && ($letter->status == 'Proses');
        });
        
        $newLetters = $letters->filter(function ($letter) {
            return $letter->status === 'Baru';
        });

        $pendingLetters = $letters->filter(function ($letter) {
            return $letter->status == 'Proses';
        });
        
        $remainingLetters = $letters->reject(function ($letter) use ($deadlineLetters, $pendingLetters,$newLetters) {
            return $deadlineLetters->contains('id', $letter->id) || $pendingLetters->contains('id', $letter->id)|| $newLetters->contains('id', $letter->id);
        });
        
        
        // Combine the two sets of letters, placing pending letters at the top
        $letters = $deadlineLetters->merge ($newLetters)->merge ($pendingLetters)->merge($remainingLetters);

        // Transform disposition_process if necessary
        $letters->transform(function ($letter) {
            $decoded = json_decode($letter->disposition_process, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $letter->disposition_process = $decoded;
            } else {
                $letter->disposition_process = [$letter->disposition_process];
            }
            return $letter;
        });

        // Implement pagination
        $paginationData = $this->customPagination($letters, $currentPage);
        if ($letters->isEmpty()) {
            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'data' => [],
                'pagination' => [
                'current_page' => 0,
                'last_page' => 0,
                'total' => 0],
                'message' => ($id === null) ? 'No letters found' : 'No outgoing letters found for provided ID'
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $paginationData['data']
            ],
            'pagination' => $paginationData['pagination'],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

        
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
public function streamOutgoingPDF($id, $type = null)
{
    // If type is not provided or null, prioritize id for Letters

    if ($type === null || $type === 'surat masuk') {
        $path = File::where('letter_id', $id)->value('path');
    } elseif ($type === 'surat keluar') {
        $path = Filebalas::where('letter_balas_id', $id)->value('path');
    }

    // If the file is not found, return a response indicating that the file was not found
    if (!$path) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 404,
            'message' => 'PDF file not found for the given ID and type',
        ], 404);
    }

    // Construct the full path to the PDF file
    $pdfPath = storage_path('app/public/' . $path);

    // Set the response headers for PDF
            $headers = [
            'Content-Type' => 'application/pdf',
        ];
    return response()->file($pdfPath, $headers);

}


public function dashboard()
{
    try {
        $incoming = Letters::query();
        $outcome = OutgoingLetter::query();
        $id = Auth::id();

        $type = User::where('id', $id)->value('type');
        $isi = "";

        switch ($type) {
            case 'Kasubag. TU':
                $isi = "Tata Usaha";
                break;
            case 'Seksi Penetapan Hak & Pendaftaran':
                $isi = "Seksi penetapan hak dan pendaftaran";
                break;
            case 'Seksi Survei & Pemetaan':
                $isi = "Seksi survei dan pemetaan";
                break;
            case 'Seksi Penataan & Pemberdayaan':
                $isi = "Seksi penataan dan pemberdayaan";
                break;
            case 'Seksi Pengadaan Tanah & Pengembangan':
                $isi = "Seksi pengadaan tanah dan pengembangan";
                break;
            case 'Seksi Pengendalian & Penanganan Sengketa':
                $isi = "Seksi pengendalian dan penanganan sengketa";
                break;
            case 'Admin':
            case 'Kepala Kantor':
                $query = Letters::where('disposition_process', '!=', 'Belum ditindak lanjuti')
                                ->orderBy('created_at', 'desc');
                $query2 = Letters::where('status', '!=', 'Pending');
                $query3 = Letters::query(); // Admin dan Kepala Kantor melihat semua surat
                break;
            default:
                return response()->json([
                    'status' => 'error',
                    'statusCode' => 403,
                    'message' => 'Unauthorized access'
                ], 403);
        }

        if ($type !== 'Admin' && $type !== 'Kepala Kantor') {
            $incoming->whereJsonContains('disposition_process', $isi);
            $outcome->where('from', 'like', "%$type%");
            $query = Letters::where('status', 'Pending')
                            ->whereJsonContains('disposition_process', $isi)
                            ->orderBy('created_at', 'desc');
            $query2 = Letters::where('status', 'Selesai')
                             ->whereJsonContains('disposition_process', $isi)
                             ->orderBy('created_at', 'desc');
            $query3 = Letters::whereJsonContains('disposition_process', $isi)
                             ->orderBy('created_at', 'desc');
        }

        $suratSudahDisposisi = $query->count();
        $totalSurat = $query3->count();
        $oneWeekAgo = Carbon::now()->subWeek()->format('Y-m-d');
        $today = Carbon::now()->format('Y-m-d');
    
        $currentDayOfWeek = Carbon::now()->dayOfWeek;

        // Count specific types of letters
        $suratBelumDitindak = Letters::where('disposition_process', 'Belum ditindak lanjuti')->count();
        $suratSudahDitindak = $query2->count();
        $suratBaru = Letters::where('read_status', '0')->count();
 
        // Aggregate letters by day for the past week
        $suratPerHari = $incoming->selectRaw('DAYNAME(letter_date) as day, COUNT(*) as count')
            ->whereBetween('letter_date', [$oneWeekAgo, $today])
            ->groupBy('day')
            ->orderByRaw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")')
            ->get();
        $suratKeluarPerHari = $outcome->selectRaw('DAYNAME(outgoing_letter_date) as day, COUNT(*) as count')
            ->whereBetween('outgoing_letter_date', [$oneWeekAgo, $today])
            ->groupBy('day')
            ->orderByRaw('FIELD(day, "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday")')
            ->get();

        // Prepare data for the chart
        $labels = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $dataSuratPerHari = array_fill(0, 6, 0);
        $dataSuratKeluarPerHari = array_fill(0, 6, 0);

        $dayMapping = [
            "Senin",
            "Selasa",
            "Rabu",
            "Kamis",
            "Jumat",
            "Sabtu"
        ];
        // Fill dataSuratPerHari with the actual counts
        foreach ($suratPerHari as $item) {
            $index = array_search($item->day, $labels);
            if ($index !== false) {
                $dataSuratPerHari[$index] = $item->count;
            }
        }

        // Fill dataSuratKeluarPerHari with the actual counts
        foreach ($suratKeluarPerHari as $item) {
            $index = array_search($item->day, $labels);
            if ($index !== false) {
                $dataSuratKeluarPerHari[$index] = $item->count;
            }
        }

        return response()->json([
            'status' => 'success', 
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'totalsurat' => $totalSurat,
                'belum_ditindaklanjuti' => $suratBelumDitindak,
                'sudah_didisposisi' => $suratSudahDisposisi,
                'sudah_ditindaklanjuti' => $suratSudahDitindak,
                'surat_baru' => $suratBaru,
                'surat_per_hari' => [
                    'label' => $dayMapping,
                    'jumlah' => $dataSuratPerHari,
                    'jumlahkeluar' => $dataSuratKeluarPerHari
                ]
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}






public function notification_list()
{
    try {
        // Retrieve all letters with status 'Proses' and disposition_process containing 'Belum ditindak lanjuti'
        $id = Auth::id();

        $query = Letters::query();
        $type = User::where('id', $id)->value('type');
        
        if ($type == 'Kasubag. TU') {
            
            $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Tata Usaha%')
                    ->orderBy('created_at', 'desc');
        } elseif ($type == 'Seksi Penetapan Hak & Pendaftaran') {
            
            $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Seksi penetapan hak dan pendaftaran%')
                    ->orderBy('created_at', 'desc');
        } elseif ($type == 'Seksi Survei & Pemetaan') {
            
            $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Seksi survei dan pemetaan%')
                    ->orderBy('created_at', 'desc');
        } elseif ($type == 'Seksi Penataan & Pemberdayaan') {
            
            $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Seksi penataan dan pemberdayaan%')
                    ->orderBy('created_at', 'desc');
        } elseif ($type == 'Seksi Pengadaan Tanah & Pengembangan') {
            
            $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')
                    ->orderBy('created_at', 'desc');
        } elseif ($type == 'Seksi Pengendalian & Penanganan Sengketa') {
            
            
             $query->where('status', 'Proses')
                    ->where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%')
                    ->orderBy('created_at', 'desc'); 
        } elseif ($type == 'Admin' || $type == 'Kepala Kantor') {
                    $query->whereIn('status', ['Proses', 'Baru'])
                    ->orderBy('created_at', 'desc');        
            
        } else {
            // Handle case for unknown types or restricted access
            return response()->json([
                'status' => 'error',
                'statusCode' => 403,
                'message' => 'Unauthorized access'
            ], 403);
        }
        $letters = $query->get();

        // Decode the disposition_process for each letter
        $letters = $letters->map(function ($letter) {
            $decoded = json_decode($letter->disposition_process, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $letter->disposition_process = $decoded;
            } else {
                $letter->disposition_process = [$letter->disposition_process];
            }

            return $letter;
        });

        // Return the JSON response with the filtered letters
        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $letters
            ]
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function show_surat(request $request)
{
    try {
        // Retrieve the current page from the request, default to 1 if not provided
        $currentPage = $request->input('page', 1);

        // Retrieve all letters with read_status = 0
        $letters = Letters::all();

        // Decode the disposition_process for each letter
        $letters->transform(function ($letter) {
            $decoded = json_decode($letter->disposition_process, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $letter->disposition_process = $decoded;
            } else {
                $letter->disposition_process = [$letter->disposition_process];
            }

            return $letter;
        });

        // Perform custom pagination
        $paginationData = $this->customPagination($letters, $currentPage);

        // Structure the response with data and pagination metadata
        return response()->json([
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Data retrieved successfully',
            'data' => [
                'letter' => $paginationData['data']],
            'pagination' => $paginationData['pagination'],
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'statusCode' => 500,
            'message' => 'Internal Server Error',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function customPagination($letters, $currentPage, $perPage = 10)
{
    // Calculate the starting index of the slice
    $startIndex = ($currentPage - 1) * $perPage;

    // Slice the collection to get the data for the current page
    $currentPageData = $letters->slice($startIndex, $perPage)->values();

    // Create a LengthAwarePaginator instance for pagination metadata
    $paginator = new LengthAwarePaginator(
        $currentPageData, // Data for the current page
        $letters->count(), // Total count of items
        $perPage, // Items per page
        $currentPage, // Current page
        ['path' => LengthAwarePaginator::resolveCurrentPath()] // Additional options
    );

    // Return an array containing pagination metadata and current page data
    return [
        'data' => $currentPageData,
        'pagination' => [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
        ],
    ];
}




}