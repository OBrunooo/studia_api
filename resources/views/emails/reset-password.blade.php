<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinição de senha</title>
</head>
<body style="
    margin:0;
    padding:0;
    background-color:#f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:30px 15px;">
            <table width="100%" cellpadding="0" cellspacing="0" style="
                max-width:520px;
                background:#ffffff;
                border-radius:12px;
                box-shadow:0 4px 12px rgba(0,0,0,0.05);
                padding:30px;
            ">
                <!-- Logo -->
                <tr>
                    <td style="text-align:left;">
                        <h1 style="
                            margin:0 0 20px 0;
                            font-size:26px;
                            color:#4f46e5;
                        ">
                            StudIA
                        </h1>
                    </td>
                </tr>

                <!-- Texto -->
                <tr>
                    <td style="
                        font-size:15px;
                        color:#111827;
                        line-height:1.6;
                    ">
                        <p style="margin:0 0 12px 0;">Olá 👋</p>

                        <p style="margin:0 0 20px 0;">
                            Você solicitou a redefinição da sua senha.  
                            Use o código abaixo no aplicativo:
                        </p>
                    </td>
                </tr>

                <!-- TOKEN -->
                <tr>
                    <td align="center">
                        <div style="
                            margin:25px 0;
                            padding:18px 0;
                            width:100%;
                            background:#f1f5ff;
                            border:1px dashed #4f46e5;
                            border-radius:10px;
                            font-size:36px;
                            font-weight:bold;
                            letter-spacing:10px;
                            color:#1e1b4b;
                            text-align:center;
                        ">
                            {{ $token }}
                        </div>
                    </td>
                </tr>

                <!-- Avisos -->
                <tr>
                    <td style="
                        font-size:14px;
                        color:#374151;
                        line-height:1.6;
                    ">
                        <p style="margin:0 0 10px 0;">
                            ⏱ Este código expira em alguns minutos.
                        </p>

                        <p style="margin:0;">
                            Se você não solicitou a redefinição, ignore este email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="padding-top:30px;">
                        <hr style="border:none;border-top:1px solid #e5e7eb;">
                        <p style="
                            margin:12px 0 0 0;
                            font-size:12px;
                            color:#6b7280;
                        ">
                            © {{ date('Y') }} StudIA · Todos os direitos reservados
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
