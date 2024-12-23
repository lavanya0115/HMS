<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationToVisitorsForHyd2024Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $visitor;
    public $event;
    /**
     * Create a new job instance.
     */
    public function __construct($visitor, $event)
    {
        $this->visitor = $visitor;
        $this->event = $event;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        sendWhatsappNotificationAfterRegister($this->visitor, $this->event);
    }
}
