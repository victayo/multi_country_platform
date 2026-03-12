<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmployeeCreatedJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(private array $employeeData)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Processing employee created event in hr service job', [
            'event_type' => $this->employeeData['event_type'] ?? $this->employeeData['event'] ?? 'EmployeeCreated',
            'event_id' => $this->employeeData['event_id'] ?? null,
            'employee_id' => $this->employeeData['data']['employee']['id'] ?? null,
        ]);
    }
}
