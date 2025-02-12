<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Exception;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'email'     => 'required|email',
                'password'  => 'required'
            ]);

            // Check validasi
            if ($validator->fails()) {
                return (new PostResource(false, 'Validasi gagal',null,$validator->errors())) 
                    ->response()
                    ->setStatusCode(422);
            }

            // Get credentials
            $credentials = $request->only('email', 'password');

            
            if(!$token = auth()->guard('api')->attempt($credentials)) {
                // Return response gagal 
                return (new PostResource(false, 'Invalid credentials',null,)) 
                    ->response()
                    ->setStatusCode(401);
            }

            // Return response sukses 
            return (new PostResource(true, 'Berhasil Login', 
                [
                    'token'   => $token   
                ]))
            ->response()
            ->setStatusCode(200);
        } catch (JWTException  $e) {
            return (new PostResource(false, 'Gagal membuat token', null, $e->getMessage()))
                ->response()
                ->setStatusCode(500);
        } catch (\Exception $e) {
            return (new PostResource(false, 'Terjadi kesalahan', null, $e->getMessage()))
                ->response()
                ->setStatusCode(500);
        }
    }
}