<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class StaticPageController extends Controller
{
    public function terms()
    {
        $termsPath = resource_path('views/terms-and-agreement-content.txt');
        $rawTermsText = file_exists($termsPath)
            ? (string) file_get_contents($termsPath)
            : 'Terms content is currently unavailable. Please contact support.';

        $normalizedTermsText = trim((string) preg_replace("/\r\n?|\n/", "\n", $rawTermsText));

        return response()->json([
            'content' => $normalizedTermsText,
        ]);
    }

    public function forgotPassword()
    {
        return response()->json([
            'message' => 'Password reset flow is not implemented yet. Please create a new account from the sign up screen.',
        ]);
    }
}
