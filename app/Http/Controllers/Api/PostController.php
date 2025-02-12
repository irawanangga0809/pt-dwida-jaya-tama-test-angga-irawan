<?php

namespace App\Http\Controllers\Api;


use App\Models\Post;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Validasi parameter
            $validated = $request->validate([
                'search' => 'nullable|string|max:255',
                'limit' => 'nullable|integer|min:1',
                'page' => 'nullable|integer|min:1'
            ]);

            // Define parameter
            $search = $validated['search'] ?? null;
            $limit = $validated['limit'] ?? null;
            $page = $validated['page'] ?? 1;

            // Create unique cache key unik berdasarkan filter
            $cacheKey = "posts_" . md5(json_encode($validated));

            // Get data dari cache jika ada, jika tidak jalankan query dan simpan ke cache, cache timer di set 60 detik sebagai percobaan
            $response = Cache::remember($cacheKey, 60, function () use ($search, $limit, $page) {
                $query = Post::query();

                if ($search) {
                    $query->where('title', 'like', "%{$search}%");
                }

                if ($limit) {
                    // Hasil Query dengan paginasi
                    $posts = $query->paginate($limit, ['*'], 'page', $page);

                    // Filter object yang ingin ditampilkan
                    return collect($posts)->only([
                        'current_page', 'data', 'last_page', 'per_page', 'total'
                    ]);
                } else {
                    return $query->get();
                }
            });
            

            // Return response
            return (new PostResource(true, 'Daftar data Post', $response, null))
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

    public function store(Request $request)
    {
        try {
            // Validasi input JSON
            $validated = $request->validate([
                'title' => 'required',
                'body' => 'required',
                'user_id' => 'required|integer|exists:users,id'
            ]);

            // Simpan data ke database
            $post = Post::create([
                'title' => $validated['title'],
                'body' => $validated['body'],
                'user_id' => $validated['user_id'],
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
            // Cek Post by Id
            $post= Post::find($id);

            if (!$post) {
                return (new PostResource(false, 'Data Post tidak ditemukan',null))
                ->response()
                ->setStatusCode(404);
            }

            // Validasi input JSON  
            $validated = $request->validate([ 
                'title' => 'sometimes',
                'body' => 'sometimes',
                'user_id' => 'sometimes|integer|exists:users,id'
            ]);

            // Update dengan data baru dari JSON
            if (isset($validated['title'])) {
                $post->title = $validated['title']; 
            }
            if (isset($validated['body'])) {
                $post->body = $validated['body'];
            }
            if (isset($validated['user_id'])) {
                $post->user_id = $validated['user_id'];
            }

            // Simpan perubahan
            $post->save();

            // Return response sukses
            return (new PostResource(true, 'Data User berhasil diubah!', $post))
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
}
