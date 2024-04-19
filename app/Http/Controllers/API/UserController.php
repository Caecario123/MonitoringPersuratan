<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
/**
 * @OA\Get(
 *      path="/api/users",
 *      summary="Get all users",
 *      description="Returns all users",
 *      tags={"Users"},
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/User")
 *          ),
 *      ),
 *      security={ {"bearerAuth": {}} }
 * )
 */

/**
 * @OA\Get(
 *      path="/api/users/{id}",
 *      summary="Get user by ID",
 *      description="Returns a single user",
 *      tags={"Users"},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of the user",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(ref="#/components/schemas/User")
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      ),
 *      security={ {"bearerAuth": {}} }
 * )
 */

/**
 * @OA\Put(
 *      path="/api/users/{id}",
 *      summary="Update user by ID",
 *      description="Update a single user",
 *      tags={"Users"},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of the user",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\RequestBody(
 *          required=true,
 *          description="Data to update the user",
 *          @OA\JsonContent(ref="#/components/schemas/UserRequest")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User updated successfully"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Validation error",
 *          @OA\JsonContent(
 *              type="object",
 *              @OA\Property(
 *                  property="error",
 *                  type="object",
 *                  @OA\Property(
 *                      property="email",
 *                      type="array",
 *                      @OA\Items(type="string")
 *                  ),
 *                  @OA\Property(
 *                      property="nama",
 *                      type="array",
 *                      @OA\Items(type="string")
 *                  ),
 *                  @OA\Property(
 *                      property="password",
 *                      type="array",
 *                      @OA\Items(type="string")
 *                  ),
 *                  @OA\Property(
 *                      property="type",
 *                      type="array",
 *                      @OA\Items(type="string")
 *                  )
 *              )
 *          )
 *      ),
 *      security={ {"bearerAuth": {}} }
 * )
 */

/**
 * @OA\Delete(
 *      path="/api/users/{id}",
 *      summary="Delete user by ID",
 *      description="Delete a single user",
 *      tags={"Users"},
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          description="ID of the user",
 *          required=true,
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="User deleted successfully"
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="User not found"
 *      ),
 *      security={ {"bearerAuth": {}} }
 * )
 */


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
}