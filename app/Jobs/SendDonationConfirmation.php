<?php

namespace App\Jobs;

use App\Notifications\DonationConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDonationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    protected $donation;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $donation)
    {
        $this->user = $user;
        $this->donation = $donation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new DonationConfirmation($this->donation));
    }
}
