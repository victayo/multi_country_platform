<?php

namespace App\Jobs;

use App\Domain\Employees\Services\EmployeeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EmployeeUpdatedJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(private array $employeeData)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Employee Updated Job executed with data: ', $this->employeeData);
        $employeeService = app(EmployeeService::class);
        $employeeService->updateFromEvent($this->employeeData);
    }
}
