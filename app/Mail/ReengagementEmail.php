<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReengagementEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Customer $customer)
    {
    }

    public function build()
    {
        return $this->subject('We miss you at SinodTech!')
            ->view('emails.reengagement')
            ->with(['customer' => $this->customer]);
    }
}