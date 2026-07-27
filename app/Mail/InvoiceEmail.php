<?php

namespace App\Mail;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Sale $sale)
    {
    }

    public function build()
    {
        $pdf = Pdf::loadView('invoices.invoice', ['sale' => $this->sale]);

        return $this->subject("Your SinodTech Invoice #{$this->sale->id}")
            ->view('emails.invoice-notification')
            ->attachData($pdf->output(), "invoice-{$this->sale->id}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}