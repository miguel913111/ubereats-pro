<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StripeOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $onboardingUrl;
    public string $type; // 'vendor' ou 'deliveryman'

    public function __construct(string $name, string $onboardingUrl, string $type = 'vendor')
    {
        $this->name = $name;
        $this->onboardingUrl = $onboardingUrl;
        $this->type = $type;
    }

    public function build()
    {
        $subject = $this->type === 'vendor' 
            ? 'Complete o seu registo de pagamentos - ' . config('app.name')
            : 'Complete o seu registo de pagamentos - ' . config('app.name');

        return $this->subject($subject)
            ->view('emails.stripe-onboarding')
            ->with([
                'name' => $this->name,
                'onboardingUrl' => $this->onboardingUrl,
                'type' => $this->type,
            ]);
    }
}
