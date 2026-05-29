<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registo de Pagamentos</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #635BFF;">Olá {{ $name }}!</h2>
        
        <p>Foi criada uma conta de pagamentos para si na nossa plataforma <strong>{{ config('app.name') }}</strong>.</p>
        
        <p>Para começar a receber os seus pagamentos automaticamente, precisa de completar o registo clicando no botão abaixo:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $onboardingUrl }}" 
               style="background-color: #635BFF; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">
                Completar Registo de Pagamentos
            </a>
        </div>
        
        <p>Ou copie e cole este link no seu navegador:</p>
        <p style="word-break: break-all; background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 13px;">
            {{ $onboardingUrl }}
        </p>
        
        <p><strong>O que vai precisar:</strong></p>
        <ul>
            <li>Documento de identidade (Cartão de Cidadão ou Passaporte)</li>
            <li>IBAN da conta bancária onde deseja receber os pagamentos</li>
        </ul>
        
        <p style="color: #666; font-size: 14px;">
            Se tiver alguma dúvida, contacte o suporte da plataforma.
        </p>
        
        <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">
        <p style="color: #999; font-size: 12px;">
            Este email foi enviado automaticamente por {{ config('app.name') }}.
        </p>
    </div>
</body>
</html>
