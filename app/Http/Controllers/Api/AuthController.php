<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterDeviceRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ApiResponse;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserRepository $users,
    ) {
    }

    public function registerDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $deviceUuid = $request->validated('device_uuid');

        $user = $this->users->firstOrCreateByDeviceUuid($deviceUuid);

        $token = $user->createToken('device-token')->plainTextToken;

        return ApiResponse::success(
            'Daftar perangkat berhasil. Gunakan token untuk autentikasi.',
            [
                'token' => $token,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => (new UserResource($user))->resolve(),
            ],
            201,
        );
    }

    /**
     * Registrasi email & kata sandi dari perangkat (butuh token guest hasil register-device).
     *
     * - Email belum ada  : upgrade akun guest menjadi akun ber-email (token guest tetap dipakai).
     * - Email sudah ada  : registrasi ulang saat ganti HP. Password dicocokkan dulu; akun lama
     *                      dipindah ke device_uuid perangkat ini, maka login otomatis ke akun tsb.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $guest = $request->user();

        $email = Str::lower(trim($request->validated('email')));
        $existing = $this->users->findByEmail($email);

        if ($existing !== null) {
            if (! Hash::check($request->validated('password'), $existing->password)) {
                return ApiResponse::error('Email sudah terdaftar. Password tidak sesuai.', 422);
            }

            if ($existing->id === $guest->id) {
                return ApiResponse::success(
                    'Akun sudah terdaftar di perangkat ini.',
                    $this->authPayload($guest),
                );
            }

            // Registrasi ulang dari perangkat baru: pindahkan akun email ke perangkat ini.
            $guest->tokens()->delete();
            $account = $this->users->reRegisterOnDevice($guest, $existing);

            return ApiResponse::success(
                'Email berhasil dipulihkan ke perangkat baru.',
                $this->authPayload($account),
            );
        }

        // Registrasi pertama: upgrade guest jadi akun ber-email.
        $user = $this->users->upgradeGuestToEmail($guest, $request->validated('name'), $email, $request->validated('password'));

        return ApiResponse::success(
            'Pendaftaran berhasil.',
            $this->authPayload($user),
            201,
        );
    }

    /**
     * Login ke akun email yang sudah ada (tanpa membuat akun baru).
     *
     * Butuh token aktif (umumnya token guest hasil register-device). Jika email cocok
     * dengan perangkat yang sedang digunakan, token baru langsung diterbitkan. Jika email
     * tersebut terdaftar di perangkat lain (ganti HP), akun dipindah ke perangkat ini dan
     * token di perangkat lama dicabut (kebijakan satu perangkat aktif).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $deviceUser = $request->user();

        $email = Str::lower(trim($request->validated('email')));
        $account = $this->users->findByEmail($email);

        if ($account === null || $account->isGuest()) {
            return ApiResponse::error('Email tidak terdaftar.', 404);
        }

        if (! Hash::check($request->validated('password'), $account->password)) {
            return ApiResponse::error('Email atau password salah.', 401);
        }

        if ($account->id === $deviceUser->id) {
            return ApiResponse::success('Login berhasil.', $this->authPayload($account));
        }

        $deviceUser->tokens()->delete();
        $account = $this->users->reRegisterOnDevice($deviceUser, $account);

        return ApiResponse::success('Login berhasil.', $this->authPayload($account));
    }

    /**
     * Keluar dari perangkat sekarang (token saat ini dihapus).
     * Digunakan untuk berganti user; masuk kembali pakai registrasi ulang (email + password).
     *
     * Selalu membalas cepat (target < 2 detik). Bila token tidak valid/sudah dicabut,
     * middleware auth:sanctum merespons 401 dengan cepat sebelum sampai ke sini.
     */
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }

        return ApiResponse::success('Berhasil keluar dari perangkat ini.');
    }

    private function authPayload($user): array
    {
        $token = $user->createToken('device-token')->plainTextToken;

        return [
            'token' => $token,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => (new UserResource($user))->resolve(),
        ];
    }
}