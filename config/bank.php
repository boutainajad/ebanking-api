<?php

return [
    'daily_transfer_limit' => env('DAILY_TRANSFER_LIMIT', 10000),
    'default_overdraft_limit' => env('DEFAULT_OVERDRAFT_LIMIT', 500),
    'default_interest_rate_epargne' => env('DEFAULT_INTEREST_RATE_EPARGNE', 3.5),
    'default_interest_rate_mineur' => env('DEFAULT_INTEREST_RATE_MINEUR', 2.0),
    'default_monthly_fee' => env('DEFAULT_MONTHLY_FEE', 50),
    'epargne_monthly_withdrawal_limit' => env('EPARGNE_MONTHLY_WITHDRAWAL_LIMIT', 3),
    'mineur_monthly_withdrawal_limit' => env('MINEUR_MONTHLY_WITHDRAWAL_LIMIT', 2),
];