<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Letters;
use App\Models\Outgoingletter;
use App\Models\Filebalas;

use App\Models\User;
use File as files;

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
    // Validasi permintaan
    $validator = Validator::make($request->all(), [
        'reference_number2' => 'required|string',
        'outgoing_letter_date' => 'required|date',
        'note' => 'nullable|string',
        'file' => 'required|mimes:pdf|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 400);
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
            return response()->json(['error' => 'Letter not found.'], 404);
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
        return response()->json(['error' => 'No PDF uploaded.'], 400);
    }

    // Redirect to dashboard with success message
    // $user = auth()->user()->type;
    // $type = $user . ".";
    return response()->json(['success' => true, 'message' => 'Data successfully updated.'], 200);
}


    public function store(Request $request)
{
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
    $data['user_id'] = $data['user_id'];    // $request->input('user_id', auth()->user()->id);

    try {
        Letters::whereId($letterid)->update(['status' => $status]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
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
        return response()->json(['error' => 'No PDF uploaded.'], 400);
    }

    // $user = auth()->user()->type;
    // $type = $user . ".";
    return response()->json(['success' => true, 'message' => 'Data successfully stored.'], 200);
}

    public function daftarbalasan($id)
    {
        
        $outgoingLetter = Outgoingletter::where('letter_id', $id)->get();
        return view('daftarbalasan', compact('outgoingLetter'));
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
        // Mencari surat dengan ID yang diberikan
        $outgoingLetter = Outgoingletter::find($id);// Jika Anda mengharapkan satu hasil

        // Memeriksa apakah surat tersebut ditemukan
        if ($outgoingLetter) {
            // Menghapus file balasan jika ada
            $filebalas = Filebalas::where('letter_balas_id', $id)->get();
            if($filebalas->isNotEmpty()){
                foreach ($filebalas as $fb) {
                    $filePath = $fb->path;
                    $pdfPath2 = 'app/public/' . $filePath;
                    unlink(storage_path($pdfPath2));
                    $fb->delete();
                }
            }
            
            // Menghapus surat
            $outgoingLetter->delete();
            
            // Mengirim response sukses
            // $user = auth()->user()->type;
            // $type = $user.".";
            // return redirect()->route($type.'dashboard')->with('message', 'Surat berhasil dihapus.');
            return response()->json(['message' => 'Surat keluar berhasil dihapus'], 200);

        } else {
            // Jika surat tidak ditemukan, kirim response error
            return response()->json(['message' => 'Letter not found'], 404);
        }
    }

}
