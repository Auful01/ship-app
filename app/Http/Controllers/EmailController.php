<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailController extends Controller
{
    public function sendMail($payload)
    {
        Http::post('https://script.google.com/macros/s/AKfycbxFNsyMXW8chGL8YhdQE1Q1yBbx5XEsq-BJeNF1a6sKoowaL_9DtcUvE_Pp0r5ootgMhQ/exec', [
            'email' => $payload['email'],
            'subject' => $payload['subject'],
            'message' => $payload['message'],
            'token' => $payload['token'],
        ]);
    }

    public function otpMail($payload)
    {
        $payload = [
            'email' => $payload["email"],
            'subject' => 'OTP Verification',
            'message' => 'Your OTP is ' . $payload["otp"] . ', Please verify your email. OTP will be expired in 5 minutes.',
            'token' => $payload["token"],
        ];
        return $this->sendMail($payload);
    }
}
