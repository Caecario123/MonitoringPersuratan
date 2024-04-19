<?php

namespace App\Http\Controllers;
use App\Http\Controllers\GdriveController;
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
    public function kirimEditbalasan(Request $request,$id) {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'reference_number2' => 'required|string', // Ubah validasi sesuai kebutuhan Anda
            'outgoing_letter_date' => 'required|date',
            'note' => 'nullable|string',
            'file' => 'required|mimes:pdf|max:2048',// Hanya menerima file PDF dengan ukuran maksimum 2MB
        ]);
       
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }        
        // Set default values for fields if they are null
        $data['reference_number2'] = $request->reference_number2;
        $data['outgoing_letter_date'] = $request->outgoing_letter_date;
        $data['note'] = $request->note;
        // Simpan juga informasi file ke tabel file
        if ($request->file('file')) {
            // Store the data into the database
            $letter = Outgoingletter::whereId($id)->update($data);
            //hapus file lama
            $filePathlama = Filebalas::where('letter_balas_id', $id)->first()->path;
            $pdfPathlama = 'app/public/' . $filePathlama;
            unlink(storage_path($pdfPathlama));
            // dd($letter->id);
            $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
            // Simpan file PDF di penyimpanan 'pdfs' dalam penyimpanan 'public'
            $pdfPath = $request->file('file')->storeAs('Keluar', $pdfName, 'public');
            // Simpan informasi file ke dalam tabel file
            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_balas_id' => $id, // Ambil nomor referensi dari input
            ];
            Filebalas::whereId($id)->update($fileData);

        } else {
            return "No PDF uploaded.";
        }
    
        // Redirect to welcome page with data
       
       
        $user = auth()->user()->type;
        $type = $user.".";
        return redirect()->route($type.'dashboard');
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

    $user = auth()->user()->type;
    $type = $user.".";
    return redirect()->route($type.'dashboard');
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
    public function deleteOutgoingLetter($id)
    {
        // Mencari surat dengan ID yang diberikan
        $outgoingLetter = Outgoingletter::find($id);// Jika Anda mengharapkan satu hasil
        $letterid=$outgoingLetter->id;

        $id_balas = Outgoingletter::where('letter_id', $letterid)->get()->pluck('id'); // Mengambil hanya ID
        $filebalas = Filebalas::where('letter_balas_id', $letterid)->get();

        // Memeriksa apakah surat tersebut ditemukan
        if ($outgoingLetter) {
            // Menghapus surat
            if($filebalas){
                foreach ($filebalas as $fb) {
                    $filePath = $fb->path;
                    $pdfPath2 = 'app/public/' . $filePath;
                    unlink(storage_path($pdfPath2));
                    $fb->delete();
                }
            }
            if($outgoingLetter)
            {
                $outgoingLetter->delete();
                
            }
            
            
            // Mengirim response sukses
            $user = auth()->user()->type;
            $type = $user.".";
            return redirect()->route($type.'dashboard')->with('message', 'Surat berhasil dihapus.');
        } else {
            // Jika surat tidak ditemukan, kirim response error
            return response()->json(['message' => 'Letter not found'], 404);
        }
    }
}
