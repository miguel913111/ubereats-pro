# 6amMart Admin - Railway Deploy Status

## ✅ O que ja foi feito

- Projeto Railway criado: 6ammart-admin
- Deploy iniciado do painel admin

## 📋 IDs Importantes

- Project ID: 73ac4a6e-3257-4dbb-b0c9-26805a95a954
- Environment ID: 6e7ce25c-3bb4-42b7-b5fe-68c81164b833
- Service ID: dbfa68a9-7260-4bfe-90c2-29989c440e70
- Deployment ID: e19d86d4-13ac-428f-84dc-53d2fa9687d8

## 🔗 Links Diretos

- Dashboard: https://railway.com/project/73ac4a6e-3257-4dbb-b0c9-26805a95a954
- Deploy: https://railway.com/project/73ac4a6e-3257-4dbb-b0c9-26805a95a954/service/dbfa68a9-7260-4bfe-90c2-29989c440e70?id=e19d86d4-13ac-428f-84dc-53d2fa9687d8

## ⏳ O que falta completar

1. Adicionar banco de dados MySQL no dashboard do Railway
2. Configurar variaveis de ambiente (DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_KEY)
3. Gerar dominio publico
4. Verificar se o build foi bem-sucedido

## 🛠 Variaveis recomendadas

APP_KEY=<gerar com php artisan key:generate --show>
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST={{ MYSQL_HOST }}
DB_PORT={{ MYSQL_PORT }}
DB_DATABASE={{ MYSQL_DATABASE }}
DB_USERNAME={{ MYSQL_USER }}
DB_PASSWORD={{ MYSQL_PASSWORD }}
