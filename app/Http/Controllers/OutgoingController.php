<?php

namespace App\Http\Controllers;
use App\Http\Controllers\GdriveController;

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

    }
    public function balasansurat(Request $request){

    // Validasi permintaan
    $validator = $request->validate([
        'reference_number2' => 'required|string', // Ubah validasi sesuai kebutuhan Anda
        'outgoing_letter_date' => 'required|date',
        'letter_id' => 'nullable|string',
        'note' => 'nullable|string',
        'user_id' => 'nullable|string',
        'status' => 'nullable|string', 
        'file' => 'required|mimes:pdf|max:2048', // Hanya menerima file PDF dengan ukuran maksimum 2MB
    ]);

    // Ambil data dari permintaan
    $data = $request->all();

    // Simpan status dari data untuk pembaruan tabel Letters
    $status = $data['status'];
    $letterid = $data['letter_id'];
    $data['user_id'] = $request->input('user_id', auth()->user()->id);
    try {
        Letters::whereId($letterid)->update(['status' => $status]);
    } catch (\Exception $e) {
        dd($e->getMessage());
    }
    unset($data['status']);

    if ($request->file('file')) {
        // Simpan data ke dalam tabel outgoingletter
        $letter = Outgoingletter::create($data);
        $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
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
        return "No PDF uploaded.";
    }

    return redirect()->route('dashboard');
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
}
