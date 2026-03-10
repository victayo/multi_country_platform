<?php

namespace App\Checklist\Services;

class ChecklistAggregator
{
    // public function aggregate(array $evaluations): array
    // {
    //     $summary = [
    //         'total' => count($evaluations),
    //         'complete' => 0,
    //         'incomplete' => 0,
    //         'missing_fields' => []
    //     ];

    //     foreach ($evaluations as $evaluation) {
    //         if ($evaluation['complete']) {
    //             $summary['complete']++;
    //         } else {
    //             $summary['incomplete']++;
    //             foreach ($evaluation['missing'] as $field) {
    //                 if (!isset($summary['missing_fields'][$field])) {
    //                     $summary['missing_fields'][$field] = 0;
    //                 }
    //                 $summary['missing_fields'][$field]++;
    //             }
    //         }
    //     }

    //     return $summary;
    // }

    public function aggregate(array $results): array
    {
        $total = count($results);

        $completed = collect($results)
            ->where('complete', true)
            ->count();

        $completionRate = $total > 0
            ? round(($completed / $total) * 100)
            : 0;

        return [
            'total_employees' => $total,
            'completed' => $completed,
            'completion_rate' => $completionRate,
            'employees' => $results
        ];
    }
}
