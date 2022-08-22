<?php

namespace App\Enum;

enum UserWalletTransactionTypeEnum: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
}