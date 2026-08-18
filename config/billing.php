<?php

return [
    'free_trial_days' => max(0, (int) env('SAAS_FREE_TRIAL_DAYS', 14)),
];
