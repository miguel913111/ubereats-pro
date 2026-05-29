# Stripe Connect — Split Payments Automático

## O que foi implementado

Sistema de pagamentos divididos (split payments) usando Stripe Connect. Quando um cliente paga:
- O **restaurante** recebe automaticamente a parte dele
- O **entregador** recebe automaticamente a parte dele  
- A **plataforma (tu)** fica com a fee fixa (0,60€ delivery / 1,00€ take away)

**Zero transferências manuais.** Tudo automático via webhook.

---

## Configuração no Stripe Dashboard

### 1. Criar conta Stripe (se ainda não tiveres)
Vai a https://dashboard.stripe.com e cria conta de empresa.

### 2. Ativar Stripe Connect
- No dashboard: **Settings > Connect settings**
- Ativa **Express** accounts (ou Standard se preferires)
- Guarda as tuas chaves:
  - `Publishable key`
  - `Secret key`

### 3. Configurar no teu Admin Panel
- Vai a: **Business Settings > Payment Method > Stripe**
- Mete:
  - **API Key**: a tua `Secret key` (ex: `sk_test_...` ou `sk_live_...`)
  - **Published Key**: a tua `Publishable key` (ex: `pk_test_...` ou `pk_live_...`)
- Ativa o Stripe

### 4. Configurar Webhook
- No Stripe Dashboard: **Developers > Webhooks**
- Adicionar endpoint:
  - **Endpoint URL**: `https://tuadominio.pt/stripe-connect/webhook`
  - **Events to listen to**:
    - `payment_intent.succeeded`
    - `account.updated`
- Guarda o **Webhook signing secret**
- Adiciona no teu `.env`:
```env
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxx
```

### 5. Correr a migration
```bash
php artisan migrate
```

---

## Como funciona o fluxo

### Registo de Vendor/Entregador
1. Admin cria/aprova vendor ou entregador
2. O sistema cria automaticamente uma **Stripe Express account**
3. Envia email (manual por agora) com link de onboarding
4. O vendor/entregador clica no link, preenche IBAN e identidade no Stripe
5. Fica ativo para receber pagamentos

### Pagamento do Cliente
1. Cliente faz pedido → app chama `POST /api/v1/stripe-connect/payment-intent`
2. Backend retorna `client_secret`
3. App usa Stripe SDK para confirmar pagamento com o `client_secret`
4. Stripe processa pagamento → envia webhook `payment_intent.succeeded`
5. Backend recebe webhook e **automaticamente** divide o dinheiro:
   - Restaurante → transfer
   - Entregador → transfer
   - Plataforma → fica com o resto

---

## Rotas disponíveis

### Web (para app/web frontend)
| Rota | Método | Descrição |
|------|--------|-----------|
| `/stripe-connect/webhook` | POST | Webhook do Stripe (sem CSRF) |
| `/stripe-connect/onboarding/success` | GET | Pós-onboarding vendor/entregador |
| `/stripe-connect/onboarding/refresh` | GET | Renova link de onboarding |

### API (para app mobile)
| Rota | Método | Auth | Descrição |
|------|--------|------|-----------|
| `/api/v1/stripe-connect/payment-intent` | POST | Não | Cria PaymentIntent (manda `order_id`) |
| `/api/v1/stripe-connect/account-status` | GET | Não | Verifica status da conta Stripe |

---

## Exemplo de uso no Frontend (JavaScript/React)

```javascript
// 1. Criar PaymentIntent
const response = await fetch('/api/v1/stripe-connect/payment-intent', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ order_id: 12345 })
});
const { client_secret } = await response.json();

// 2. Confirmar pagamento com Stripe Elements
const stripe = await loadStripe('pk_tua_chave_publica');
const result = await stripe.confirmPayment({
  clientSecret: client_secret,
  elements,
  confirmParams: {
    return_url: 'https://tuadominio.pt/payment-success',
  },
});
```

---

## Campos novos na base de dados

### Tabela `stores`
- `stripe_account_id` — ID da conta Stripe Connect
- `stripe_onboarding_complete` — 1 se onboarding completo, 0 se não

### Tabela `delivery_men`
- `stripe_account_id` — ID da conta Stripe Connect
- `stripe_onboarding_complete` — 1 se onboarding completo, 0 se não

---

## Fee da plataforma (configurável)

Atualmente hardcoded no `StripeConnectController`:
- **Delivery**: 0,60€ (60 cêntimos)
- **Take Away**: 1,00€

Para alterar, edita no método `createPaymentIntent`:
```php
$platformFeeCents = $order->order_type === 'take_away' ? 100 : 60;
```

---

## Testar em modo Sandbox

1. No Stripe Dashboard, alterna para **Test mode**
2. Usa a `Secret key` de teste no Admin Panel
3. Faz um pedido na app
4. Usa cartão de teste: `4242 4242 4242 4242` (qualquer data futura, qualquer CVC)
5. Verifica no Stripe Dashboard se o split foi feito corretamente

---

## Troubleshooting

### "Restaurante ainda não configurou pagamentos"
O vendor ainda não completou o onboarding Stripe. Verifica no admin se tem `stripe_onboarding_complete = 1`.

### Webhook não recebido
- Verifica se a URL do webhook está acessível publicamente (não localhost)
- Verifica se `STRIPE_WEBHOOK_SECRET` está correto no `.env`
- Verifica logs em `storage/logs/laravel.log`

### Transfer falhou
- Verifica se a connected account tem `charges_enabled = true` e `payouts_enabled = true`
- Verifica se há saldo suficiente na conta da plataforma

---

## Próximos passos recomendados

1. **Enviar email automático** ao vendor/entregador com o link de onboarding
2. **Painel admin** para ver status de onboarding de cada vendor
3. **Stripe Billing** para subscrições mensais (fase 2 do teu modelo)
4. **Reembolsos automáticos** — implementar webhook `charge.refunded`
