<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use App\Models\User;
use App\Enum\UserWalletTransactionTypeEnum;

class UserWalletJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The user object
     *
     * @var \App\Models\User
     */
    protected object $user;

    /**
     * @var string 
     */
    protected string $type; 

    /**
     * @var float
     */
    protected float $amount;

    /**
     * @var string 
     */
    protected string|null $comment;

    /**
     * The number of attempts to complete the task.
     *
     * @var int
     */
    public $tries = 5;
    
    /**
     * The maximum number of unhandled exceptions allowed.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Create a new job instance.
     *
     * @param App\Models\User $user
     * @param string          $type 
     * @param float           $amount
     * @param string          $comment
     * 
     * @return void
     */
    public function __construct(User $user, $type, $amount, $comment)
    {
        $this->user = $user;
        $this->type = Str::lower($type);
        $this->amount = (float) $amount;
        $this->comment = $comment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->user;
        $type = $this->type; 
        $amount = $this->amount;
        $comment = $this->comment;
        
        if ($type == UserWalletTransactionTypeEnum::DEPOSIT->value) {
            $type = UserWalletTransactionTypeEnum::DEPOSIT;
            $user->balance += $amount;
        } else if ($type == UserWalletTransactionTypeEnum::WITHDRAW->value) {
            if (!$user->canWithdraw($amount)) {
                $this->logError('Not enough money on balance',
                    [
                        'user_id' => $user->id,
                        'type' => $type,
                        'amount' => $amount,
                        'comment' => $comment
                    ]
                );

                return 0;
            }

            $type = UserWalletTransactionTypeEnum::WITHDRAW;
            $user->balance -= $amount;
        } else {
            $this->logError('Unknown operation type',
                [
                    'user_id' => $user->id,
                    'type' => $type,
                    'amount' => $amount,
                    'comment' => $comment
                ]
            );

            return 0;
        }

        DB::transaction(function () use ($user, $type, $amount, $comment) {
            $this->user->transactions()->create([
                'uuid' => 0,
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'comment' => $comment
            ]);

            $user->save();
        });
    }

    /**
     * @param string $message
     * @param array $data 
     * 
     * @return void
     */
    private function logError($message, $data): void
    {
        Log::channel('wallet')->error($message, $data);
    }
}
