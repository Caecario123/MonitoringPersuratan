<?php

namespace App\Http\Controllers;
use App\Models\Filebalas;
use App\Models\Letters;
use App\Models\OutgoingLetter;
use App\Models\User;
use File as files;
use Illuminate\Pagination\LengthAwarePaginator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Validate request
        $validator = Validator::make($request->all(), [
           'reference_number2' => 'nullable|string',
            'outgoing_letter_date' => 'nullable|date',
            'note' => 'nullable|string',
            'file' => 'nullable|mimes:pdf|max:2048',
            'status'=>'nullable|string'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['status' => false, 'statusCode' => 400, 'message' => $validator->errors()->first()], 400);
        }
    
        try {
            $data = $request->only(['reference_number2', 'outgoing_letter_date', 'note']);
            $status = $request->only(['status']);
            $statusvalue=$status['status'];
            // Find the letter to update
            $letter = OutgoingLetter::find($id);
            if (!$letter) {
                return response()->json(['status' => false, 'statusCode' => 404, 'message' => 'Letter not found.'], 404);
            }
    
            if ($request->file('file')) {
                // Handle the file update
                $existingFile = Filebalas::where('letter_balas_id', $id)->first();
                if ($existingFile) {
                    // Delete the old file
                    Storage::disk('public')->delete($existingFile->path);
                }
    
                // Store the new file
                $pdfName = time() . '_' . $request->file('file')->getClientOriginalName();
                $pdfPath = $request->file('file')->storeAs('Keluar', $pdfName, 'public');
    
                // Update file information or create new file record
                $fileData = [
                    'name' => $pdfName,
                    'path' => $pdfPath,
                    'letter_balas_id' => $id,
                ];
                Filebalas::updateOrCreate(['letter_balas_id' => $id], $fileData);
    
                // Add file data to the response
                $data['file'] = $fileData;
            }
            $letterid=OutgoingLetter::whereId($id)->value('letter_id');

            // Update the letter data
            $letter->update($data);
            Letters::whereId($letterid)->update(['status' => $statusvalue]);

            return response()->json([
                'message' => 'Letter updated successfully',
                'status' => true,
                'statusCode' => 200,
                'data' => [
                    'replyletter' => $letter,
                    'filebalas' => isset($fileData) ? $fileData : null
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

    public function store(Request $request,$id)
    {
        
        try {
            // Validasi permintaan
            $validator = $request->validate([
                'reference_number2' => 'required|unique:outgoingletters',
                'outgoing_letter_date' => 'required|date',
                'note' => 'nullable|string',
                'from' => 'nullable|string',
                'user_id' => 'nullable|string',
                'letter_id' => 'nullable|string',
                'status' => 'nullable|string',
                'file' => 'required|mimes:pdf|max:2048',
            ]);
           
            // Ambil data dari permintaan
            $data = $request->all();
            $ids = Auth::id();
            $type = User::where('id', $ids)->value('type');
            
            // Simpan status dari data untuk pembaruan tabel Letters
            $status = $data['status'];
            $letterid = $id;
            $data['user_id'] = $ids;// $request->input('user_id', auth()->user()->id);
            $data['letter_id'] = $letterid;
            $data['from'] = $type; // $request->input('user_id', auth()->user()->id);
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
    

   public function daftarbalasan(Request $request, $id = null)
{
    try {
        $currentPage = $request->input('page', 1); // Dapatkan halaman saat ini dari permintaan
        $perPage = 10; // Tentukan jumlah item per halaman

        // Inisialisasi kueri utama
        
        $query = ($id === null) ? OutgoingLetter::query() : OutgoingLetter::where('letter_id', $id);
        
        $ids = Auth::id();
        $type = User::where('id', $ids)->value('type');

        // Define the base query
        // Apply filters based on user type
        if ($type == 'Kasubag. TU') {
            $query->where('from', 'like', '%Tata Usaha%');
        } elseif ($type == 'Seksi Penetapan Hak & Pendaftaran') {
            $query->where('from', 'like', '%Seksi penetapan hak dan pendaftaran%');
        } elseif ($type == 'Seksi Survei & Pemetaan') {
            $query->where('from', 'like', '%Seksi survei dan pemetaan%');
        } elseif ($type == 'Seksi Penataan & Pemberdayaan') {
            $query->where('from', 'like', '%Seksi penataan dan pemberdayaan%');
        } elseif ($type == 'Seksi Pengadaan Tanah & Pengembangan') {
            $query->where('from', 'like', '%Seksi pengadaan tanah dan pengembangan%');
        } elseif ($type == 'Seksi Pengendalian & Penanganan Sengketa') {
            $query->where('from', 'like', '%Seksi pengendalian dan penanganan sengketa%');
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
        // Pencarian berdasarkan kata kunci jika tersedia
        $search = $request->input('typing');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('from', 'LIKE', "%$search%")
                  ->orWhere('note', 'LIKE', "%$search%")
                  // Tambahkan kolom lain yang ingin Anda cari di sini
                  ->orWhere('reference_number2', 'LIKE', "%$search%");
            });
        }

        // Eksekusi kueri untuk mengambil data sesuai dengan pencarian dan filter jika ada
        $outgoingLetters = $query->get();

        // Periksa jika data ditemukan atau tidak
        if ($outgoingLetters->isEmpty()) {
            return response()->json([
                'status' => true,
                'statusCode' => 200,
                'data' => [],
                'pagination' => [
                'current_page' => 0,
                'last_page' => 0,
                'total' => 0],
                'message' => ($id === null) ? 'No outgoing letters found' : 'No outgoing letters found for provided ID'
            ], 200);
        }

        // Lakukan custom pagination untuk data yang ditemukan
        $outgoingLettersPagination = $this->customPagination($outgoingLetters, $currentPage, $perPage);

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'data' => [
                'replyletter' => $outgoingLettersPagination['data']
            ],
            'pagination' => $outgoingLettersPagination['pagination'],
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

public function customPagination($items, $currentPage, $perPage = 10)
{
    // Calculate the starting index of the slice
    $startIndex = ($currentPage - 1) * $perPage;

    // Slice the collection to get the data for the current page
    $currentPageData = $items->slice($startIndex, $perPage)->values();

    // Create a LengthAwarePaginator instance for pagination metadata
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
        $currentPageData, // Data for the current page
        $items->count(), // Total count of items
        $perPage, // Items per page
        $currentPage, // Current page
        ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()] // Additional options
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
