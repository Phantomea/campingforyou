<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Setting;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private ?string $qr1Png = null;
    private ?string $qr2Png = null;

    public function __construct(
        public Booking $booking,
        public array $schedule = [],
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Vaša žiadosť o rezerváciu karavanu bola prijatá – CampingForYou',
        );
    }

    public function content(): Content
    {
        $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:9000'), '/');
        $statusUrl   = $frontendUrl . '/stav-rezervacie/' . $this->booking->token;

        $service         = $this->booking->service;
        $serviceUrl      = $service?->slug ? $frontendUrl . '/services/' . $service->slug : null;
        $serviceImageUrl = null;
        if ($service) {
            $images = $service->images ?? [];
            if (!empty($images)) {
                $serviceImageUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/') . '/storage/' . $images[0];
            } elseif ($service->image) {
                $serviceImageUrl = $service->image;
            }
        }

        $iban = Setting::get('iban') ?? '';

        $schedule      = $this->schedule;
        $singlePayment = $schedule['single_payment'] ?? true;
        $upfront       = (float) ($schedule['upfront_amount'] ?? $this->booking->total_price ?? 0);
        $remaining     = (float) ($schedule['remaining_amount'] ?? 0);
        $deadline1     = $schedule['payment_deadline'] ?? null;
        $deadline2     = $schedule['full_payment_deadline'] ?? null;

        $msg1 = $singlePayment
            ? 'Rezervace - ' . $this->booking->id
            : 'Zaloha - '    . $this->booking->id;
        $msg2 = 'Doplatek - ' . $this->booking->id;

        $this->qr1Png = $upfront > 0 ? $this->makeQrPng($this->buildSpayd($upfront, $iban, $msg1)) : null;
        $this->qr2Png = (!$singlePayment && $remaining > 0)
            ? $this->makeQrPng($this->buildSpayd($remaining, $iban, $msg2))
            : null;

        return new Content(
            view: 'emails.booking.received',
            with: [
                'statusUrl'           => $statusUrl,
                'serviceUrl'          => $serviceUrl,
                'serviceImageUrl'     => $serviceImageUrl,
                'pickupTime'          => Setting::get('pickup_time'),
                'returnTime'          => Setting::get('return_time'),
                'latestPickupTime'    => Setting::latestPickupTime($this->booking->date_from),
                'singlePayment'       => $singlePayment,
                'upfrontAmount'       => $upfront,
                'remainingAmount'     => $remaining,
                'deadline1'           => $deadline1,
                'deadline2'           => $deadline2,
                'iban'                => $iban,
                'bankName'            => Setting::get('bank_name') ?? '',
                'bankCode'            => Setting::get('bank_code') ?? '',
                'bankBic'             => Setting::get('bank_bic') ?? '',
                'accountHolderName'   => Setting::get('account_holder_name') ?? '',
                'msg1'                => $msg1,
                'msg2'                => $msg2,
                'qr1'                 => $this->qr1Png,
                'qr2'                 => $this->qr2Png,
            ],
        );
    }

    public function attachments(): array
    {
        $singlePayment = $this->schedule['single_payment'] ?? true;
        $id            = $this->booking->id;
        $result        = [];

        if ($this->qr1Png !== null) {
            $png    = $this->qr1Png;
            $name   = $singlePayment ? "qr-rezervace-{$id}.png" : "qr-zaloha-{$id}.png";
            $result[] = Attachment::fromData(fn () => $png, $name)->withMime('image/png');
        }
        if ($this->qr2Png !== null) {
            $png    = $this->qr2Png;
            $result[] = Attachment::fromData(fn () => $png, "qr-doplatek-{$id}.png")->withMime('image/png');
        }

        return $result;
    }

    private function buildSpayd(float $amount, string $iban, string $msg): string
    {
        $ibanClean = preg_replace('/\s+/', '', $iban);
        $amountStr = number_format($amount, 2, '.', '');
        $id        = $this->booking->id;
        $rn        = Setting::get('account_holder_name') ?? '';

        $parts = ["SPD*1.0*ACC:{$ibanClean}"];
        if ($rn !== '') {
            $parts[] = "RN:{$rn}";
        }
        $parts[] = "AM:{$amountStr}";
        $parts[] = "CC:CZK";
        $parts[] = "X-VS:{$id}";
        $parts[] = "MSG:{$msg}";

        return implode('*', $parts);
    }

    private function makeQrPng(string $text): string
    {
        $qr     = new QrCode($text, size: 200, margin: 8);
        $writer = new PngWriter();
        return $writer->write($qr)->getString();
    }
}
