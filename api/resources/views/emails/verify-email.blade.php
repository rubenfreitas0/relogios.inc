<!-- resources/views/emails/verify-email.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 40px;">
        
        <h2 style="color: #333; margin-bottom: 20px;">
            Verificação de Email
        </h2>
        
        <p style="color: #555;">
            Bem-vindo à RELOGIOS.inc! Use o código abaixo para verificar o seu email:
        </p>
        
        <div style="background-color: #f8f8f8; border-radius: 8px; padding: 20px; 
                    text-align: center; margin: 20px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #000;">
                {{ $code }}
            </span>
        </div>
        
        <p style="color: #999; font-size: 14px;">
            Este código expira em 30 minutos.
        </p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Se não criou uma conta, ignore este e-mail.
        </p>

    </div>
</body>
</html>