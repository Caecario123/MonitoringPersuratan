<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    /**
     * Menampilkan daftar semua pengguna.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::all();
        return response()->json(['data' => $users], 200);
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
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['data' => $user], 200);
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
            return response()->json(['message' => 'User not found'], 404);
        }

        $validator = validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'nama' => 'required',
            'password' => 'nullable',
            'type' => 'nullable|in:0,1,2,3,4,5,6,7',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user->email = $request->email;
        $user->name = $request->nama;
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->has('type')) {
            $user->type = $request->type;
        }
        $user->save();

        return response()->json(['message' => 'User updated successfully'], 200);
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
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
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
            'password'  => 'required',
            'type' => 'required|in:0,1,2,3,4,5,6,7',
        ]);

        // Jika validasi gagal, kembalikan respon error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
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
        return response()->json(['message' => 'User created successfully'], 201);
    }

}