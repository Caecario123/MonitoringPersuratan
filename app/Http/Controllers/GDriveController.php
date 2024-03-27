<?php

namespace App\Http\Controllers;

use File;
use App\Models\File as files;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yaza\LaravelGoogleDriveStorage\Gdrive;

class GdriveController extends Controller
{
    //
    public function upload(Request $request){
        $data = $request->all();
        $path = $data['path'];
        $filename = $data['name'];
        
        Storage::disk('google')->put($filename, File::get($path));
        return response()->json(['success'=>true]);
    }
    public function get_file(Request $request, $id){

        $file = files::where('id', $id)->get('name');
        $arrayData = json_decode($file, true); // Konversi JSON ke array asosiatif
        $name = $arrayData[0]['name'];

        // return$name;
        // $path = public_path().'/'.'fileA.pdf';
        $filename = $name;

        $data2 = Storage::disk('google')->get($filename);
        
        $fileContent = Storage::disk('google')->get($filename);

        return response($fileContent, 200)
            ->header('Content-Type', 'application/pdf');
        
    }
    public function uploadbalas(Request $request){
        $data = $request->all();
        $path = $data['path'];
        $filename = $data['name'];
        
        Storage::disk('google')->put($filename, File::get($path));
        return response()->json(['success'=>true]);
    }
    public function get_filebalas(Request $request, $id){

        $file = files::where('id', $id)->get('name');
        $arrayData = json_decode($file, true); // Konversi JSON ke array asosiatif
        $name = $arrayData[0]['name'];

        // return$name;
        // $path = public_path().'/'.'fileA.pdf';
        $filename = $name;

        $data2 = Storage::disk('google')->get($filename);
        
        $fileContent = Storage::disk('google')->get($filename);

        return response($fileContent, 200)
            ->header('Content-Type', 'application/pdf');
        
    }
}
