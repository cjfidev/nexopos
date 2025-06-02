<?php

namespace App\Listeners;

use App\Events\ProcurementReturnAfterCreateEvent;
use App\Services\ProcurementService;
use App\Services\ProviderService;
use App\Services\TransactionService;

class ProcurementReturnAfterCreateEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        public ProcurementService $procurementService,
        public ProviderService $providerService,
        public TransactionService $transactionService,
    ) {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object $event
     * @return void
     */
    public function handle( ProcurementAfterCreateEvent $event )
    {
        $this->procurementService->refresh( $event->procurement );
        $this->providerService->computeSummary( $event->procurement->provider );
        $this->procurementService->handleProcurement( $event->procurement );
        $this->transactionService->handleProcurementTransaction( $event->procurement );
    }
}
