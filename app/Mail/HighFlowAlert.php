<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class HighFlowAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $clientName;
    public $flowRate;
    public $cubicMeter;
    public $threshold;

    public function __construct($clientName, $flowRate, $cubicMeter, $threshold)
    {
        $this->clientName  = $clientName;
        $this->flowRate    = $flowRate;
        $this->cubicMeter  = $cubicMeter;
        $this->threshold   = $threshold;
    }

    public function build()
    {
        return $this->subject('⚠️ High Water Flow Alert - MEEDMO Magallanes')
                    ->view('mails.high_flow_alert');
    }
}