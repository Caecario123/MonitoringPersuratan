<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Filebalas;
use App\Models\Letters;
use App\Models\OutgoingLetter;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class HomeController extends Controller
{
    public function dashboard(){
        $user = User::all(); // Menggunakan model Letter untuk mengambil data

        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page

        $datas = $data2->merge($data);
        return view('dashboard');
    }
    public function adminDashboard()
    {
        try {
            $users = User::all(); // Mengambil semua data pengguna
            $letters = Letters::all(); // Mengambil semua data surat
            $files = File::all(); // Mengambil semua data file

            // Menggabungkan data surat dan data file menjadi satu koleksi
            $datas = $letters->merge($files);

            // Mengembalikan koleksi data dalam format JSON
            return response()->json(['success' => true, 'users' => $users, 'datas' => $datas], 200);
        } catch (\Exception $e) {
            // Mengembalikan pesan kesalahan jika terjadi kesalahan dalam pengambilan data
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function adminDashboards()
    // {
    //     $user = User::all(); // Menggunakan model Letter untuk mengambil data

    //     $data = Letters::all(); // Assuming you're fetching data for a welcome page
    //     $data2 = File::all(); // Assuming you're fetching data for a welcome page

    //     $datas = $data2->merge($data);

        
    //     return view('adminDashboard', compact('datas'));
    // }
    public function kakanDashboard()
    {
        return view('kakanDashboard');
    }
    public function tatausahaDashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Tata Usaha%')->get();

        // $datas = Letters::where('disposition_process', 'Tata Usaha')->get();
        return view('tatausahaDashboard', compact('datas'));
    }
    public function seksi1Dashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Seksi penetapan hak dan pendaftaran%')->get();

        // $datas = Letters::where('disposition_process', 'Seksi penetapan hak dan pendaftaran')->get();
        return view('seksi1Dashboard', compact('datas'));
    }
    public function seksi2Dashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Seksi survei dan pemetaan%')->get();

        // $datas = Letters::where('disposition_process', 'Seksi survei dan pemetaan')->get();
        return view('seksi2Dashboard', compact('datas'));
    }
    public function seksi3Dashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Seksi penataan dan pemberdayaan%')->get();

        // $datas = Letters::where('disposition_process', 'Seksi penataan dan pemberdayaan')->get();
        return view('seksi3Dashboard', compact('datas'));
    }
    public function seksi4Dashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')->get();

        // $datas = Letters::where('disposition_process', 'like', '%Seksi pengadaan tanah dan pengembangan%')->get();
        return view('seksi4Dashboard', compact('datas'));
    }
    public function seksi5Dashboard()
    {
        $user = User::all();
        $datas = Letters::where('disposition_process', 'like', '%Seksi pengendalian dan penanganan sengketa%')->get();

        // $datas = Letters::where('disposition_process', 'Seksi pengendalian dan penanganan sengketa')->get();
        return view('seksi5Dashboard', compact('datas'));
    }
    public function index(){
        $data = User::get();
        return view('index',compact('data'));
    }
    public function create(){
        return view('create');
    }
    public function store(Request $request){

        $validator = validator::make($request->all(),[
            'email' => 'required|email',
            'nama'  => 'required',
            'password'  => 'required',
            'type' => 'required|in:0,1,2,3,4,5,6,7',
        ]);
        if($validator->fails()) return redirect()->back()->withInput()->withErrors($validator);

        $data['email'] = $request->email;
        $data['name'] = $request->nama;
        $data['password'] = Hash::make($request->password);
        $data['type'] = $request->type;

        User::create($data);
        return redirect()->route('admin.index');
    }

    public function edit(Request $request,$id){
        $data = User::find($id);
        return view('edit',compact('data'));
    }

    public function update(Request $request,$id){
        $validator = validator::make($request->all(),[
            'email' => 'required|email',
            'nama'  => 'required',
            'password'  => 'nullable',
            'type' => 'nullable'
        ]);
        if($validator->fails()) return redirect()->back()->withInput()->withErrors($validator);

        $data['email'] = $request->email;
        $data['name'] = $request->nama;
        $data['type'] = $request->type;

        if($request->password){
            $data['password'] = Hash::make($request->password);
        }
        User::whereId($id)->update($data);
        return redirect()->route('admin.index');
    }

    public function delete(Request $request,$id){
        $data = User::find($id);
        if($data){
            $data->delete();
        }
        return redirect()->route('admin.index');
    }
    public function index2()
    {
        $user = User::all(); // Menggunakan model Letter untuk mengambil data

        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page

        $datas = $data2->merge($data);

        return view('seksi1Dashboard', compact('datas'));
    }
    public function newletterlist()
    {
        $data = Letters::where('read_status', '0')->get();

        $user = User::all(); // Menggunakan model Letter untuk mengambil data

        return view('newLetterList', compact('data'));
    }
    public function tambahsurat(){
        $user = User::all(); // Menggunakan model Letter untuk mengambil data
        return view('tambahsurat');
    }
    public function editLetter(Request $request,$id){
        $data = Letters::find($id);
        // dd($data);
        return view('editsurat',compact('data'));

    }
    public function store2(Request $request) {
        // dd($request);
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
            'read_status' => 'nullable|string', // Hanya menerima file PDF dengan ukuran maksimum 2MB
            'file' => 'required|mimes:pdf|max:2048', // Hanya menerima file PDF dengan ukuran maksimum 2MB
        ]);
       
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }        
        // Set default values for fields if they are null
        $data = $request->all();
        $data['read_status'] = $request->input('read_status', '0');
        $data['disposition_note'] = $request->input('disposition_note', '-');
        $data['disposition_process'] = $request->input('disposition_process', 'Belum ditindak lanjuti');
        $data['status'] = $request->input('status', 'Pending');
        $data['user_id'] = $request->input('user_id', auth()->user()->id);
        
        // Simpan juga informasi file ke tabel file
        if ($request->file('file')) {
            // Store the data into the database
            $letter = Letters::create($data);
            // dd($letter->id);
            $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
            // Simpan file PDF di penyimpanan 'pdfs' dalam penyimpanan 'public'
            $pdfPath = $request->file('file')->storeAs('Masuk', $pdfName, 'public');
            // Simpan informasi file ke dalam tabel file
            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_id' => $letter->id, // Ambil nomor referensi dari input
            ];
            File::create($fileData);

        } else {
            return "No PDF uploaded.";
        }
    
        // Redirect to welcome page with data
       
        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page
        $datas = $data2->merge($data);
        $user = auth()->user()->type;
        $type = $user.".";
        return redirect()->route($type.'dashboard');
    }
    public function store3(Request $request,$id) {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'reference_number' => 'nullable',
            'letters_type' => 'required',
            'letter_date' => 'required|date',
            'received_date' => 'nullable|date',
            'from' => 'nullable|string',
            'description' => 'nullable|string',
             // Hanya menerima file PDF dengan ukuran maksimum 2MB
            'file' => 'required|mimes:pdf|max:2048', // Hanya menerima file PDF dengan ukuran maksimum 2MB
        ]);
       
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }        
        // Set default values for fields if they are null
        $data['reference_number'] = $request->reference_number;
        $data['letters_type'] = $request->letters_type;
        $data['letter_date'] = $request->letter_date;
        $data['received_date'] = $request->received_date;
        $data['from'] = $request->from;
        $data['description'] = $request->description;

        // Simpan juga informasi file ke tabel file
        if ($request->file('file')) {
            // Store the data into the database
            $letter = Letters::whereId($id)->update($data);
            //hapus file lama
            $filePathlama = File::where('letter_id', $id)->first()->path;
            $pdfPathlama = 'app/public/' . $filePathlama;
            unlink(storage_path($pdfPathlama));
            // dd($letter->id);
            $pdfName = time().'_'.$request->file('file')->getClientOriginalName();
            // Simpan file PDF di penyimpanan 'pdfs' dalam penyimpanan 'public'
            $pdfPath = $request->file('file')->storeAs('Masuk', $pdfName, 'public');
            // Simpan informasi file ke dalam tabel file
            $fileData = [
                'name' => $pdfName,
                'path' => $pdfPath,
                'letter_id' => $id, // Ambil nomor referensi dari input
            ];
            File::whereId($id)->update($fileData);

        } else {
            return "No PDF uploaded.";
        }
    
        // Redirect to welcome page with data
       
        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page
        $datas = $data2->merge($data);
        $user = auth()->user()->type;
        $type = $user.".";
        return redirect()->route($type.'dashboard');
    }
    public function disposisi(Request $request,$id){
        $data = Letters::find($id);
        // dd($data);
        return view('disposisi',compact('data'));

    }
    
    public function disposisikan(Request $request,$id){
       
        $validator = Validator::make($request->all(), [
            'disposition_date' => 'nullable|date',
            'disposition_note' => 'nullable|string',
            'disposition_process' => 'nullable|string',
            'disposition_process2' => 'nullable|string',

        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }  
        $date = Carbon::now()->toDateString();
        $process1 = $request->disposition_process;
        $process2 = $request->disposition_process2;
        $combinedProcess = trim($process1 . ' ' . $process2);
        // dd($combinedProcess);
        $data['disposition_process'] = $combinedProcess;
        $data['disposition_date'] = $request->input('disposition_date', $date);
        $data['disposition_note'] = $request->disposition_note;
        // $data['disposition_process'] = $request->disposition_process;
        
        Letters::whereId($id)->update($data);
        $user = auth()->user()->type;
        $type = $user.".";
        return redirect()->route($type.'dashboard');   
    }
    public function detailSurat(Request $request,$id){
        $data['read_status'] = $request->input('read_status', '1');

        Letters::whereId($id)->update($data);

        $letter = Letters::find($id);
        // dd($data);
        return view('detailsurat',compact('letter'));

    }


    public function streamPDF($id){
        $path = File::where('letter_id', $id)->value('path');

        $pdfPath = storage_path('app/public/' . $path);

        $headers = [
            'Content-Type' => 'application/pdf',
        ];
        return response()->file($pdfPath, $headers);
    
    }
    public function deleteLetter($id)
    {
        // Mencari surat dengan ID yang diberikan
        $letter = Letters::find($id);
        $outgoingLetter = Outgoingletter::where('letter_id', $id)->get(); // Jika Anda mengharapkan satu hasil
        $files = File::where('letter_id', $id)->get();
        $filePath = File::where('letter_id', $id)->first()->path;
        $id_balas = Outgoingletter::where('letter_id', $id)->get()->pluck('id'); // Mengambil hanya ID
        $filebalas = Filebalas::where('letter_balas_id', $id_balas)->get();
        // Memeriksa apakah surat tersebut ditemukan
        if ($letter) {
            // Menghapus surat
            if($filebalas){
                foreach ($filebalas as $fb) {
                    $filePath2 = $fb->path;
                    $pdfPath2 = 'app/public/' . $filePath2;
                    unlink(storage_path($pdfPath2));

                    $fb->delete();
                }
            }
            if($files){
                foreach ($files as $file) {
                    $pdfPath = 'app/public/' . $filePath;
                    unlink(storage_path($pdfPath));

                    $file->delete();
                    $letter->delete();
                }
            }
            if($outgoingLetter)
            {
                foreach ($outgoingLetter as $ol) {
                    $ol->delete();
                }
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
