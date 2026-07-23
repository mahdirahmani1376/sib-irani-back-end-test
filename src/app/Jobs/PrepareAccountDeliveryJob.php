<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\AccountDeliveryExternalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

#[Backoff(1,5,15)]
#[Tries(3)]
class PrepareAccountDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public AccountDeliveryExternalService $accountDeliveryService;
    public Account $account;

    public function __construct(
        public $accountId
    )
    {
        $this->accountDeliveryService = app(AccountDeliveryExternalService::class);
        $this->account = Account::find($this->accountId);
    }

    public function handle(): void
    {
        $this->accountDeliveryService->deliverAccount($this->account);
    }

    public function failed(?Throwable $exception): void
    {
        // Send user notification of failure, etc...
        $this->accountDeliveryService->handleFailedAccountDelivery($this->account);
    }
}
