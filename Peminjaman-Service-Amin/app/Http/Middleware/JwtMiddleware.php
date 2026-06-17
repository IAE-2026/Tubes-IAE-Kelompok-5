<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: Bearer token missing',
            ], 401);
        }

        try {
            // Ambil JWKS dari SSO Dosen, di-cache 1 jam
            $jwks = Cache::remember('sso_jwks', 3600, function () {
                $ssoUrl = rtrim(env('SSO_URL', 'https://iae-sso.virtualfri.id'), '/');
                $response = Http::timeout(10)->get($ssoUrl . '/api/v1/auth/jwks');

                if (!$response->successful()) {
                    $response = Http::timeout(10)->get($ssoUrl . '/.well-known/jwks.json');
                }

                if (!$response->successful()) {
                    throw new \Exception('Failed to fetch JWKS from SSO');
                }
                return $response->json();
            });

            // Decode header JWT untuk ambil kid
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                throw new \Exception('Invalid JWT format');
            }

            $header  = json_decode(base64_decode(strtr($tokenParts[0], '-_', '+/')), true);
            $payload = json_decode(base64_decode(strtr($tokenParts[1], '-_', '+/')), true);

            // Validasi expiry manual
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                throw new \Exception('Token has expired');
            }

            // Validasi signature menggunakan public key dari JWKS
            $kid        = $header['kid'] ?? null;
            $publicKey  = $this->findPublicKey($jwks, $kid);

            if (!$publicKey) {
                throw new \Exception('No matching key found in JWKS');
            }

            $dataToVerify = $tokenParts[0] . '.' . $tokenParts[1];
            $signature    = base64_decode(strtr($tokenParts[2], '-_', '+/'));

            $valid = openssl_verify($dataToVerify, $signature, $publicKey, OPENSSL_ALGO_SHA256);

            if ($valid !== 1) {
                throw new \Exception('Token signature verification failed');
            }

            // Petakan user SSO ke konteks request lokal
            $profile = $payload['profile'] ?? [];
            $roleName = $payload['role'] ?? 'warga';
            $sub = $payload['sub'] ?? 'user';
            $email = $profile['email'] ?? $payload['email'] ?? (filter_var($sub, FILTER_VALIDATE_EMAIL) ? $sub : $sub . '@ktp.iae.id');
            $name = $profile['name'] ?? $payload['name'] ?? $sub ?? 'SSO User';

            // Find or create role locally
            $role = \App\Models\Role::firstOrCreate(['name' => $roleName]);

            // Find or create user locally
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)), // dummy password
                ]
            );

            // Update user role if it has changed
            if ($user->role_id !== $role->id) {
                $user->update(['role_id' => $role->id]);
            }

            $request->merge([
                'sso_payload' => $payload,
                'sso_sub'     => $sub,
                'sso_email'   => $email,
                'sso_role'    => $roleName,
                'local_user'  => $user,
            ]);

            Log::info('SSO JWT verified', [
                'sub'   => $payload['sub']   ?? '-',
                'email' => $payload['email'] ?? '-',
            ]);

        } catch (\Exception $e) {
            Log::warning('JWT verification failed: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: ' . $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }

    /**
     * Cari public key dari JWKS berdasarkan kid.
     * Jika kid tidak cocok, ambil key pertama yang tersedia.
     */
    private function findPublicKey(array $jwks, ?string $kid): mixed
    {
        $keys = $jwks['keys'] ?? [];

        foreach ($keys as $keyData) {
            if ($kid === null || ($keyData['kid'] ?? null) === $kid) {
                return $this->buildPublicKeyFromJwk($keyData);
            }
        }

        // Fallback: pakai key pertama
        if (!empty($keys)) {
            return $this->buildPublicKeyFromJwk($keys[0]);
        }

        return null;
    }

    /**
     * Konversi JWK (RSA) ke PEM public key tanpa GMP extension.
     */
    private function buildPublicKeyFromJwk(array $jwk): mixed
    {
        if (($jwk['kty'] ?? '') !== 'RSA') {
            return null;
        }

        $n = $jwk['n'] ?? '';
        $e = $jwk['e'] ?? '';

        if (empty($n) || empty($e)) {
            return null;
        }

        // Decode base64url directly to raw binary bytes (no GMP required)
        $nBin = base64_decode(strtr($n, '-_', '+/'));
        $eBin = base64_decode(strtr($e, '-_', '+/'));

        // Strip leading null bytes if any
        $nBin = ltrim($nBin, "\x00");
        // Prepend 0x00 if the most significant bit is set (positive integer in two's complement DER)
        if (ord($nBin[0]) >= 0x80) {
            $nBin = "\x00" . $nBin;
        }

        $eBin = ltrim($eBin, "\x00");
        if (ord($eBin[0]) >= 0x80) {
            $eBin = "\x00" . $eBin;
        }

        // Build DER-encoded RSA public key (PKCS#1 wrapped in SubjectPublicKeyInfo)
        $rsaKey = $this->encodeRsaPublicKey($nBin, $eBin);

        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split(base64_encode($rsaKey), 64, "\n")
            . "-----END PUBLIC KEY-----\n";

        return openssl_pkey_get_public($pem);
    }

    private function encodeRsaPublicKey(string $nBin, string $eBin): string
    {
        $derN   = "\x02" . $this->derLength(strlen($nBin)) . $nBin;
        $derE   = "\x02" . $this->derLength(strlen($eBin)) . $eBin;
        $seq    = "\x30" . $this->derLength(strlen($derN) + strlen($derE)) . $derN . $derE;

        // OID for rsaEncryption: 1.2.840.113549.1.1.1
        $oid    = "\x30\x0d\x06\x09\x2a\x86\x48\x86\xf7\x0d\x01\x01\x01\x05\x00";
        $bitStr = "\x03" . $this->derLength(strlen($seq) + 1) . "\x00" . $seq;

        return "\x30" . $this->derLength(strlen($oid) + strlen($bitStr)) . $oid . $bitStr;
    }

    private function derLength(int $len): string
    {
        if ($len < 128) {
            return chr($len);
        }
        $hex = dechex($len);
        if (strlen($hex) % 2 !== 0) {
            $hex = '0' . $hex;
        }
        $bytes = hex2bin($hex);
        return chr(0x80 | strlen($bytes)) . $bytes;
    }
}
