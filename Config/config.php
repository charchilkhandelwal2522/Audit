<?php

return [
    'name' => 'Audit',

    /*
    |--------------------------------------------------------------------------
    | Partial Completion Weight
    |--------------------------------------------------------------------------
    |
    | This value determines how much weight a partial completion carries
    | when calculating the audit score. Default is 0.5 (50% of full completion).
    |
    */
    'partial_completion_weight' => 0.5,

    /*
    |--------------------------------------------------------------------------
    | Score Threshold Alert
    |--------------------------------------------------------------------------
    |
    | When an audit score is below this threshold, alerts will be sent.
    |
    */
    'score_threshold_alert' => 70,
];

