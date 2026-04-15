<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function forgotPassword()
    {
        return view('forgot-password');
    }

    public function termsAndAgreement()
    {
        $termsPath = resource_path('views/terms-and-agreement-content.txt');
        $rawTermsText = file_exists($termsPath)
            ? (string) file_get_contents($termsPath)
            : 'Terms content is currently unavailable. Please contact support.';

        $normalizedTermsText = trim((string) preg_replace("/\r\n?|\n/", "\n", $rawTermsText));

        return view('terms-and-agreement', [
            'termsContent' => $normalizedTermsText,
        ]);
    }

    public function acceptTermsAndAgreement(Request $request)
    {
        $request->session()->put('terms_and_agreement_accepted', true);

        return redirect('/signup.php')->with('terms_accepted_success', 'Terms & Agreement accepted. You can now complete registration.');
    }

    public function support()
    {
        return view('support.index');
    }
}
