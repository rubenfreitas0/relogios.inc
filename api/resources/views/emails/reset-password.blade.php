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
            Clique no botão abaixo para criar uma nova password:
        </p>
        
        <a href="{{ config('app.frontend_url') }}/reset-password?token={{ $token }}&email={{ $email }}" 
           style="display: inline-block; background-color: #000; color: #fff; 
                  padding: 14px 28px; text-decoration: none; border-radius: 6px; 
                  margin: 20px 0; font-weight: bold;">
            Redefinir Password
        </a>
        
        <p style="color: #999; font-size: 14px;">
            Este link expira em 60 minutos.
        </p>

        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        
        <p style="color: #999; font-size: 12px;">
            Se não pediu esta alteração, ignore este e-mail. A sua password não será alterada.
        </p>

    </div>
</body>
</html>