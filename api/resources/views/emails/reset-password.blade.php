<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 40px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; padding: 40px;">
        
        <h2 style="color: #333; margin-bottom: 20px;">
            Recuperação de Password
        </h2>
        
        <p style="color: #555;">
            Recebemos um pedido para redefinir a password da sua conta.
        </p>
        
        <p style="color: #555;">
            Copie o código de 6 dígitos abaixo e insira-o na página de redefinição de password do site:
        </p>
        
        <div style="background-color: #f4f4f4; border: 1px solid #ddd; padding: 15px; text-align: center; font-size: 28px; font-weight: bold; letter-spacing: 6px; margin: 20px 0; border-radius: 6px; color: #000; font-family: monospace;">
            {{ $token }}
        </div>
        
        <p style="color: #999; font-size: 14px;">
            Este código expira em 15 minutos.
        </p>

        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Se não pediu esta alteração, ignore este e-mail. A sua password não será alterada.
        </p>

    </div>
</body>
</html>