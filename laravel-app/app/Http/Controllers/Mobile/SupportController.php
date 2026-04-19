<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;

class SupportController extends Controller
{
    public function index()
    {
        return response()->json([
            'prompts' => [
                'How do I upgrade my subscription plan?',
                'My listing is not showing to buyers',
                'How can I message a supplier?',
                'How do I fix login problem?',
            ],
            'rules' => [
                [
                    'keys' => ['upgrade', 'plan', 'subscription', 'billing', 'price', 'payment'],
                    'reply' => 'To change plan, open Subscription, choose a plan, and confirm. Pro and Enterprise activate the verified badge.',
                ],
                [
                    'keys' => ['listing', 'visible', 'showing', 'inactive', 'stock', 'supplier'],
                    'reply' => 'Check your listing status in View Listings. Keep status as active, set stock above zero, and confirm price is entered correctly.',
                ],
                [
                    'keys' => ['login', 'password', 'sign in', 'account locked', 'cannot access'],
                    'reply' => 'For login issues, use Forgot Password from login page, then check your email. If issue remains, update profile email or contact support through Messages.',
                ],
                [
                    'keys' => ['message', 'chat', 'inbox', 'reply', 'buyer'],
                    'reply' => 'Open Messages to send or reply. Keep messages clear with product name, quantity, location, and timeline for faster responses.',
                ],
                [
                    'keys' => ['order', 'delivery', 'cancel', 'pending', 'processing'],
                    'reply' => 'Order updates appear on your dashboard and messages. For delivery or status problems, contact the supplier first, then escalate through support if unresolved.',
                ],
            ],
        ]);
    }
}
