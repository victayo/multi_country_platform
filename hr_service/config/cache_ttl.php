<?php

return [
    // Checklist summaries are invalidated on employee mutations, so this mainly acts as a safety TTL.
    'checklist_country_summary' => (int) env('CACHE_TTL_CHECKLIST_COUNTRY_SUMMARY_MINUTES', 30),

    // Cache used by HR employee endpoints.
    'employee_list_pages' => (int) env('CACHE_TTL_EMPLOYEE_LIST_PAGES_MINUTES', 5),
    'employee_detail' => (int) env('CACHE_TTL_EMPLOYEE_DETAIL_MINUTES', 2),

    // Suggested defaults for additional cache key families.
    'country_steps_metadata' => (int) env('CACHE_TTL_COUNTRY_STEPS_METADATA_MINUTES', 1440),
    'feature_flags' => (int) env('CACHE_TTL_FEATURE_FLAGS_MINUTES', 5),
    'expensive_reports' => (int) env('CACHE_TTL_EXPENSIVE_REPORTS_MINUTES', 15),
    'reference_lookups' => (int) env('CACHE_TTL_REFERENCE_LOOKUPS_MINUTES', 10080),
    'negative_lookup' => (int) env('CACHE_TTL_NEGATIVE_LOOKUP_MINUTES', 1),
];
