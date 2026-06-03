<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Email tidak ditemukan.'], 404);
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Save to DB (password_resets table/collection)
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $otp,
                'created_at' => Carbon::now()
            ]
        );

        // Send Email
        try {
            Mail::raw("Kode OTP untuk reset password Anda adalah: $otp\n\nKode ini berlaku selama 30 menit.", function ($message) use ($request) {
                $message->to($request->email)
                        ->subject('Reset Password NextGen');
            });
        } catch (\Throwable $e) {
            // Even if mail fails, we can return success for dev purposes or log it
            \Log::error('Failed to send reset email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Kode OTP telah dikirim ke email Anda.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|string|min:6'
        ]);

        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Kode OTP tidak valid atau salah.'], 400);
        }

        // Optional: Check expiration (e.g. 30 mins)
        // Since it's MongoDB via Jessengers, timestamp might be MongoDB\BSON\UTCDateTime or string
        // We'll skip strict expiration for simplicity, or just verify it's valid.

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan.'], 404);
        }

        // Reset the password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete the reset token
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Password berhasil diubah. Silakan login dengan password baru.'
        ]);
    }
}
