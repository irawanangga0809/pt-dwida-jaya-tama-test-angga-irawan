<?php

namespace App\Http\Controllers\Api;

use App\Models\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            
            // Validasi input JSON
            $validated = $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            // Simpan data ke database
            $post = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            // Return response sukses
            return (new PostResource(true, 'Data User berhasil ditambahkan!', $post))
                ->response()
                ->setStatusCode(201);
        } catch (ValidationException $e) {
            return (new PostResource(false, 'Validasi gagal', null, $e->errors()))
                ->response()
                ->setStatusCode(422);
        } catch (QueryException $e) {
            return (new PostResource(false, 'Terjadi kesalahan pada database', null, null))
                ->response()
                ->setStatusCode(500);
        } catch (\Exception $e) {
            return (new PostResource(false, 'Terjadi kesalahan server', null, $e->getMessage()))
                ->response()
                ->setStatusCode(500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Cek user by Id
            $user = User::find($id);

            if (!$user) {
                return (new PostResource(false, 'Data User tidak ditemukan',null))
                ->response()
                ->setStatusCode(404);
            }

            // Validasi input JSON  
            $validated = $request->validate([ 
                'name' => 'sometimes',
                'email' => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'sometimes'
            ]);

            // Update dengan data baru dari JSON
            if (isset($validated['name'])) {
                $user->name = $validated['name']; 
            }
            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }
            if (isset($validated['password'])) {
                $user->password = $validated['password'];
            }

            // Simpan perubahan
            $user->save();

            // Return response sukses
            return (new PostResource(true, 'Data User berhasil diubah!', $user))
                ->response()
                ->setStatusCode(200);
                
        } catch (ValidationException $e) {
            return (new PostResource(false, 'Validasi gagal', null, $e->errors()))
                ->response()
                ->setStatusCode(422);
        } catch (QueryException $e) {
            return (new PostResource(false, 'Terjadi kesalahan pada database', null, null))
                ->response()
                ->setStatusCode(500);
        } catch (\Exception $e) {
            return (new PostResource(false, 'Terjadi kesalahan server', null, $e->getMessage()))
                ->response()
                ->setStatusCode(500);
        }
    }

    public function getUserPosts($id)
    {
        try {
            // Validasi Id
            if (!is_numeric($id)) {
                return (new PostResource(false, 'ID harus angka', null,null))
                ->response()
                ->setStatusCode(400);
            }

            // Ambil user dengan relasi posts
            $user = User::with('posts')->find($id);

            // Jika user tidak ditemukan
            if (!$user) {
                return (new PostResource(false, 'User tidak ditemukan', null,null))
                ->response()
                ->setStatusCode(404);
            }

            // Jika user ada tapi tidak punya postingan
            if ($user->posts->isEmpty()) {
                return (new PostResource(false, 'User tidak memiliki postingan', null,null))
                ->response()
                ->setStatusCode(404);
            }

            // Jika data Post ditemukan, return response sukses
            return (new PostResource(true, 'Daftar data Post', $user->posts,null))
                ->response()
                ->setStatusCode(200);

        } catch (ModelNotFoundException $e) {
            return (new PostResource(false, 'User tidak ditemukan', null, $e->getMessage()))
                ->response()
                ->setStatusCode(404);
        } catch (QueryException $e) {
            return (new PostResource(false, 'Terjadi kesalahan dalam query database', null, null))
                ->response()
                ->setStatusCode(500);
        } catch (\Exception $e) {
            return (new PostResource(false, 'Terjadi kesalahan pada server', null, $e->getMessage()))
            ->response()
            ->setStatusCode(500);
        }
    }

    public function delete($id)
    {
        try {
            // Validasi Id
            if (!is_numeric($id)) {
                return (new PostResource(false, 'ID harus angka', null,null))
                ->response()
                ->setStatusCode(400);
            }

            DB::transaction(function () use ($id) {
                // Search data User by Id
                $user = User::findOrFail($id);

                // Hapus data Post
                $user->posts()->delete();

                // Hapus datauser
                $user->delete();
            });

            // Return response sukses
            return (new PostResource(true, "Data User dan Post dengan ID $id telah dihapus.", null))
                ->response()
                ->setStatusCode(200);
                
        } catch (ValidationException $e) {
            return (new PostResource(false, 'Validasi gagal', null, $e->errors()))
                ->response()
                ->setStatusCode(422);
        } catch (QueryException $e) {
            return (new PostResource(false, 'Terjadi kesalahan pada database', null, null))
                ->response()
                ->setStatusCode(500);
        } catch (\Exception $e) {
            return (new PostResource(false, 'Terjadi kesalahan server', null, $e->getMessage()))
                ->response()
                ->setStatusCode(500);
        }
    }
}
