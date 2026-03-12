<?php

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/ws-test', function () {
    return view('test');
})->name('ws-test');

Route::get('/test', function () {
    return redirect()->route('ws-test');
});

Route::get('/ws-test/emit', function (Request $request) {
    $country = strtoupper((string) $request->query('country', 'USA'));

    $employeePayload = [
        'id' => (int) $request->query('employee_id', 9999),
        'name' => (string) $request->query('name', 'Realtime'),
        'last_name' => (string) $request->query('last_name', 'Tester'),
        'salary' => (int) $request->query('salary', 100000),
        'country' => strtolower($country),
        'updated_at' => now()->toIso8601String(),
    ];

    $checklistPayload = [
        'country' => $country,
        'total_employees' => (int) $request->query('total_employees', 1),
        'completed' => (int) $request->query('completed', 1),
        'completion_rate' => (float) $request->query('completion_rate', 100.0),
        'updated_at' => now()->toIso8601String(),
    ];

    broadcast(new class ($country, $employeePayload) implements ShouldBroadcastNow {
        public function __construct(
            private string $country,
            private array $employee
        ) {}

        public function broadcastOn(): array
        {
            return [new Channel("employees.{$this->country}")];
        }

        public function broadcastAs(): string
        {
            return 'employee.updated';
        }

        public function broadcastWith(): array
        {
            return ['employee' => $this->employee];
        }
    });

    broadcast(new class ($country, $checklistPayload) implements ShouldBroadcastNow {
        public function __construct(
            private string $country,
            private array $summary
        ) {}

        public function broadcastOn(): array
        {
            return [new Channel("checklist.{$this->country}")];
        }

        public function broadcastAs(): string
        {
            return 'checklist.updated';
        }

        public function broadcastWith(): array
        {
            return $this->summary;
        }
    });

    return response()->json([
        'ok' => true,
        'country' => $country,
        'channels' => ["employees.{$country}", "checklist.{$country}"],
        'events' => ['employee.updated', 'checklist.updated'],
    ]);
})->name('ws-test.emit');
