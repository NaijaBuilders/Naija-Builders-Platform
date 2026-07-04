<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your password</title>
</head>
<body style="margin:0; padding:0; background-color:#F2F5FA; font-family:Arial, Helvetica, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F2F5FA; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background-color:#FFFFFF; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td style="background-color:#1F4FA3; padding:22px 28px;">
                            <span style="color:#FFFFFF; font-size:18px; font-weight:bold; letter-spacing:0.3px;">NaijaBuilders</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 28px 12px;">
                            <p style="margin:0 0 8px; color:#12203A; font-size:20px; font-weight:bold;">
                                Reset your password
                            </p>
                            <p style="margin:0; color:#5A6B85; font-size:14px; line-height:22px;">
                                {{ $recipientName ? 'Hi '.$recipientName.',' : 'Hi,' }}
                                we received a request to reset your NaijaBuilders password. Enter the code below in the app to continue.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:20px 28px;">
                            <div style="display:inline-block; background-color:#EEF3FB; border:1px solid #D5E0F2; border-radius:12px; padding:16px 32px;">
                                <span style="color:#123B7A; font-size:32px; font-weight:bold; letter-spacing:10px;">{{ $code }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:4px 28px 28px;">
                            <p style="margin:0 0 6px; color:#5A6B85; font-size:13px; line-height:20px;">
                                This code expires in <strong style="color:#12203A;">{{ $ttlMinutes }} minutes</strong>.
                            </p>
                            <p style="margin:0; color:#5A6B85; font-size:13px; line-height:20px;">
                                If you did not ask to reset your password, ignore this email &mdash; your password will stay the same.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#F7F9FC; border-top:1px solid #E6ECF5; padding:18px 28px;">
                            <p style="margin:0; color:#8A97AB; font-size:12px; line-height:18px;">
                                NaijaBuilders &middot; Building materials &amp; construction services marketplace<br>
                                This is an automated message. Please do not reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
