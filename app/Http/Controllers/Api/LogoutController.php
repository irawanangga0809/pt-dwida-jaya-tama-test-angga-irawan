<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Tymon\JWTAuth\Exceptions\JWTException;

class LogoutController extends Controller
{
    public function __invoke(Request $request)
    {        
        try {
            // Ambil token dari request
            $token = JWTAuth::getToken();

            if (!$token) {
                return (new PostResource(false, 'Token tidak ditemukan', null, null))
                    ->response()
                    ->setStatusCode(400);
            }

            // Invalidasi token agar tidak bisa digunakan lagi
            JWTAuth::invalidate($token);

            return (new PostResource(true, 'Logout Berhasil!', null, null))
                    ->response()
                    ->setStatusCode(200);
        } catch (JWTException $e) {
            return (new PostResource(false, 'Gagal logout, terjadi kesalahan.', null, $e->getMessage()))
                    ->response()
                    ->setStatusCode(500);

        }
    }
}