<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Verifikasi Email - Reborns</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f3f4f6;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #f9fafb;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .header h1 {
            margin: 0;
            color: #111827;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .content h2 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .content p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.6;
            margin-top: 0;
            margin-bottom: 30px;
        }
        .button-wrap {
            text-align: center;
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff !important;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 9999px;
            box-shadow: 0 4px 14px 0 rgba(249, 115, 22, 0.39);
        }
        .footer {
            background-color: #f9fafb;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }
        .footer p {
            color: #9ca3af;
            font-size: 13px;
            line-height: 1.5;
            margin: 0;
        }
        .footer a {
            color: #f97316;
            text-decoration: underline;
        }
        .subcopy {
            font-size: 12px;
            color: #6b7280;
            margin-top: 20px;
            word-break: break-all;
            text-align: left;
            background-color: #f3f4f6;
            padding: 16px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <img src="{{ config('app.url') }}/images/logo_with_text.png" alt="Reborns Logo" style="height: 40px; width: auto; max-width: 100%;">
            </div>
            
            <div class="content">
                <h2>Verifikasi Alamat Email Anda</h2>
                <p>Halo{{ $user->name ? ' ' . $user->name : '' }},<br><br>Terima kasih telah mendaftar di Reborns! Sebelum memulai, silakan verifikasi alamat email Anda dengan mengklik tombol di bawah ini.</p>
                
                <div class="button-wrap">
                    <a href="{{ $url }}" class="button">Verifikasi Email</a>
                </div>
                
                <p>Jika Anda tidak merasa membuat akun ini, Anda dapat mengabaikan email ini dengan aman.</p>
                
                <div class="subcopy">
                    Jika Anda kesulitan mengklik tombol "Verifikasi Email", salin dan tempel URL di bawah ini ke dalam browser web Anda:<br><br>
                    <a href="{{ $url }}">{{ $url }}</a>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
