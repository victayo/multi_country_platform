<?php

namespace App\Jobs;

use App\Domain\Employees\Services\EmployeeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmployeeDeletedJob implements ShouldQueue
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
        $eventType = $this->employeeData['event_type'] ?? $this->employeeData['event'] ?? 'unknown';
        $eventId = $this->employeeData['event_id'] ?? null;
        $employeeId = $this->employeeData['data']['employee']['id'] ?? null;

        Log::info('Processing employee deleted event', [
            'event_type' => $eventType,
            'event_id' => $eventId,
            'employee_id' => $employeeId,
        ]);

        try {
            $employeeService = app(EmployeeService::class);
            $employeeService->deleteFromEvent($this->employeeData);

            Log::info('Employee deleted event processed successfully', [
                'event_type' => $eventType,
                'event_id' => $eventId,
                'employee_id' => $employeeId,
            ]);
        } catch (Throwable $exception) {
            Log::error('Failed processing employee deleted event', [
                'event_type' => $eventType,
                'event_id' => $eventId,
                'employee_id' => $employeeId,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }
}
