<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;



class UserController extends Controller
{
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
{
    // Validasi bahwa email dan password harus diisi
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Cek jika validasi gagal
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Cek jika validasi gagal
    if ($validator->fails()) {
        // Respon jika keduanya kosong
        if ($validator->errors()->has('email') && $validator->errors()->has('password')) {
            return response()->json([
                'status' => false,
                'statusCode' => 400,
                'message' => 'Email dan password wajib diisi.'
            ], 400);
        }
        
        // Respon jika email wajib diisi
        if ($validator->errors()->has('email')) {
            return response()->json([
                'status' => false,
                'statusCode' => 400,
                'message' => 'Email wajib diisi.'
            ], 400);
        }

        // Respon jika password wajib diisi
        if ($validator->errors()->has('password')) {
            return response()->json([
                'status' => false,
                'statusCode' => 400,
                'message' => 'Password wajib diisi.'
            ], 400);
        }
    }

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('MyApp')->plainTextToken;

        return response()->json([
            'status' => true,
            'statusCode' => 200,
            'message' => 'Login berhasil',
            'token' => $token
        ], 200);
    }

    return response()->json([
        'status' => false,
        'statusCode' => 401,
        'message' => 'Email atau password salah'
    ], 401);
}


    /**
     * Menampilkan daftar semua pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
{
    $users = User::all();
    return response()->json([
        'status' => true,
        'statusCode' => 200,
        'message' => 'Menampilkan semua data users',
        'data' => $users
    ], 200);
}

    /**
     * Menampilkan detail pengguna berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'statusCode' => 404, 'message' => 'User tidak ditemukan'], 404);
        }
        return response()->json(['status' => true, 'statusCode' => 200, 'message' => 'Menampikan data user', 'data' => $user], 200);
    }

    /**
     * Mengupdate pengguna berdasarkan ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'status' => false,
            'statusCode' => 404,
            'message' => 'User tidak ditemukan'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'email' => [
            'nullable',
            'email',
            Rule::unique('users')->ignore($user->id),
        ],
        'nama' => 'nullable',
        'type' => 'nullable|in:0,1,2,3,4,5,6,7',
    ]);

    // Check if at least one field is being updated
    if (
        !$request->has('email') &&
        !$request->has('nama') &&
        !$request->has('type')
    ) {
        return response()->json([
            'status' => false,
            'statusCode' => 422,
            'message' => 'Tidak ada data yang diubah'
        ], 422);
    }

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'statusCode' => 422,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    if ($request->has('email')) {
        $user->email = $request->email;
    }
    if ($request->has('nama')) {
        $user->name = $request->nama;
    }
    if ($request->has('type')) {
        $user->type = $request->type;
    }
    $user->save();

    return response()->json([
        'status' => true,
        'statusCode' => 200,
        'message' => 'User berhasil diupdate'
    ], 200);
}



    /**
     * Menghapus pengguna berdasarkan ID.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'status' => false,
            'statusCode' => 404,
            'message' => 'User tidak ditemukan'
        ], 404);
    }
    $user->delete();

    return response()->json([
        'status' => true,
        'statusCode' => 200,
        'message' => 'User berhasil dihapus'
    ], 200);
}
    /**
     * Simpan pengguna baru.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeUser(Request $request)
{
    // Validasi data masukan
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'nama'  => 'required',
        'password'  => 'required|min:8',
        'type' => 'required|in:0,1,2,3,4,5,6,7',
    ]);

    // Jika validasi gagal, kembalikan respon error
    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'statusCode' => 422,
            'errors' => $validator->errors()
        ], 422);
    }

    // Buat data pengguna baru
    $user = new User();
    $user->email = $request->email;
    $user->name = $request->nama;
    $user->password = Hash::make($request->password);
    $user->type = $request->type;

    // Simpan pengguna baru ke database
    $user->save();

    // Kembalikan respon sukses
    return response()->json([
        'status' => true,
        'statusCode' => 201,
        'message' => 'User berhasil dibuat'
    ], 201);
}
public function changePassword(Request $request, $id)
{
    $user = User::find($id);
    if (!$user) {
        return response()->json([
            'status' => false,
            'statusCode' => 404,
            'message' => 'User tidak ditemukan'
        ], 404);
    }

    // Validate the request data
    $validator = Validator::make($request->all(), [
        'old_password' => 'required',
        'new_password' => 'required|min:8',
        'confirm_password' => 'required|same:new_password',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'statusCode' => 422,
            'message' => 'Validation error',
            'errors' => $validator->errors()
        ], 422);
    }

    // Check if the old password matches
    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json([
            'status' => false,
            'statusCode' => 422,
            'message' => 'Password lama salah'
        ], 422);
    }

    // Update the password
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json([
        'status' => true,
        'statusCode' => 200,
        'message' => 'Password berhasil diupdate'
    ], 200);
}
}

