<?php

namespace App\Services;

use App\Models\BusinessSetting;
use App\Mail\StripeOnboardingMail;
use Illuminate\Support\Facades\Mail;
use Stripe\StripeClient;

class StripeConnectService
{
    private ?StripeClient $stripe = null;

    public function __construct()
    {
        $secretKey = $this->getSecretKey();
        if ($secretKey) {
            $this->stripe = new StripeClient($secretKey);
        }
    }

    public function getSecretKey(): ?string
    {
        try {
            $payment = BusinessSetting::where('key', 'stripe')->first();
            if ($payment) {
                $config = json_decode($payment->value, true);
                return $config['api_key'] ?? null;
            }
        } catch (\Exception $e) {
            info('Stripe config error: ' . $e->getMessage());
        }
        return null;
    }

    public function isConfigured(): bool
    {
        return $this->stripe !== null;
    }

    /**
     * Criar conta Express no Stripe Connect
     */
    public function createExpressAccount(string $email, string $country = 'PT'): ?array
    {
        if (!$this->stripe) {
            return null;
        }

        try {
            $account = $this->stripe->accounts->create([
                'type' => 'express',
                'country' => $country,
                'email' => $email,
                'capabilities' => [
                    'transfers' => ['requested' => true],
                    'card_payments' => ['requested' => true],
                ],
                'settings' => [
                    'payouts' => [
                        'schedule' => ['interval' => 'daily'],
                    ],
                ],
            ]);

            $accountLink = $this->stripe->accountLinks->create([
                'account' => $account->id,
                'refresh_url' => url('/stripe/onboarding/refresh?account_id=' . $account->id),
                'return_url' => url('/stripe/onboarding/success?account_id=' . $account->id),
                'type' => 'account_onboarding',
            ]);

            return [
                'account_id' => $account->id,
                'onboarding_url' => $accountLink->url,
            ];
        } catch (\Exception $e) {
            info('Stripe Connect create account error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar se o onboarding está completo
     */
    public function checkOnboardingStatus(string $accountId): bool
    {
        if (!$this->stripe) {
            return false;
        }

        try {
            $account = $this->stripe->accounts->retrieve($accountId);
            return $account->charges_enabled && $account->payouts_enabled;
        } catch (\Exception $e) {
            info('Stripe Connect check status error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Criar PaymentIntent para um pedido
     */
    public function createPaymentIntent(int $amountCents, string $currency = 'eur', array $metadata = []): ?array
    {
        if (!$this->stripe) {
            return null;
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => $currency,
                'automatic_payment_methods' => ['enabled' => true],
                'transfer_group' => $metadata['transfer_group'] ?? null,
                'metadata' => $metadata,
            ]);

            return [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
            ];
        } catch (\Exception $e) {
            info('Stripe Connect PaymentIntent error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Transferir fundos para uma connected account
     */
    public function createTransfer(int $amountCents, string $currency, string $destinationAccountId, string $transferGroup, string $description = ''): bool
    {
        if (!$this->stripe) {
            return false;
        }

        try {
            $this->stripe->transfers->create([
                'amount' => $amountCents,
                'currency' => $currency,
                'destination' => $destinationAccountId,
                'transfer_group' => $transferGroup,
                'description' => $description,
            ]);
            return true;
        } catch (\Exception $e) {
            info('Stripe Connect Transfer error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gerar link de onboarding para conta existente
     */
    public function generateOnboardingLink(string $accountId): ?string
    {
        if (!$this->stripe) {
            return null;
        }

        try {
            $accountLink = $this->stripe->accountLinks->create([
                'account' => $accountId,
                'refresh_url' => url('/stripe-connect/onboarding/refresh?account_id=' . $accountId),
                'return_url' => url('/stripe-connect/onboarding/success?account_id=' . $accountId),
                'type' => 'account_onboarding',
            ]);

            return $accountLink->url;
        } catch (\Exception $e) {
            info('Stripe Connect onboarding link error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Enviar email de onboarding
     */
    public function sendOnboardingEmail(string $email, string $name, string $onboardingUrl, string $type = 'vendor'): bool
    {
        try {
            Mail::to($email)->send(new StripeOnboardingMail($name, $onboardingUrl, $type));
            return true;
        } catch (\Exception $e) {
            info('Stripe Connect email error: ' . $e->getMessage());
            return false;
        }
    }

    public function getStripeClient(): ?StripeClient
    {
        return $this->stripe;
    }
}
