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
        $user = User::all(); // Menggunakan model Letter untuk mengambil data

        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page

        $datas = $data2->merge($data);

        
        return view('adminDashboard', compact('datas'));
    }
    public function kakanDashboard()
    {
        return view('kakanDashboard');
    }
    public function tatausahaDashboard()
    {
        return view('tatausahaDashboard');
    }
    public function seksi1Dashboard()
    {
        $user = User::all(); // Menggunakan model Letter untuk mengambil data

        $data = Letters::all(); // Assuming you're fetching data for a welcome page
        $data2 = File::all(); // Assuming you're fetching data for a welcome page

        $datas = $data2->merge($data);

        return view('seksi1Dashboard', compact('datas'));
    }
    public function seksi2Dashboard()
    {
        return view('seksi2Dashboard');
    }
    public function seksi3Dashboard()
    {
        return view('seksi3Dashboard');
    }
    public function seksi4Dashboard()
    {
        return view('seksi4Dashboard');
    }
    public function seksi5Dashboard()
    {
        return view('seksi5Dashboard');
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
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }  
        $date = Carbon::now()->toDateString();

        $data['disposition_date'] = $request->input('disposition_date', $date);
        $data['disposition_note'] = $request->disposition_note;
        $data['disposition_process'] = $request->disposition_process;
        
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
}
