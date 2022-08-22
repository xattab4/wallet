<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Enum\UserWalletTransactionTypeEnum;
use App\Jobs\UserWalletJob;
use App\Models\User;

class UserWalletCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:wallet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Top up or deduct user balance';

    /**
     * User 
     * 
     * @var null|object
     */
    protected mixed $user; 

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $email = $this->askEmail('Email');
      
        $this->info("Find {$this->user->name} <{$this->user->email}> BALANCE " . $this->user->balance);
       
        if ($this->user->balance > 0) {
            $choice = [
                UserWalletTransactionTypeEnum::DEPOSIT->value, 
                UserWalletTransactionTypeEnum::WITHDRAW->value
            ];
        } else {
            $choice = [
                UserWalletTransactionTypeEnum::DEPOSIT->value
            ];
        }

        $depositOrWithdraw = $this->choice(
            'Deposit or withdraw?',
            $choice
        );

        $comment = $this->ask('Comment');
        $amount = $this->askSumm('Summ', $depositOrWithdraw);

        UserWalletJob::dispatch($this->user, $depositOrWithdraw, $amount, $comment)->onQueue('default');

        $this->info("User {$this->user->name} <{$this->user->email}> BALANCE " . $this->user->balance);

        return 1;
    }

    /**
     * @param      $message
     * @param null $default
     * 
     * @return string
     */
    protected function askEmail($message, $default = null): string
    {
        do {
            $email = $this->ask($message, $default);
        } while (!$this->checkIssetUser($email));

        return $email;
    }

    /**
     * @param        $message
     * @param string $type
     * 
     * @return string
     */
    protected function askSumm($message, $type): string
    {
        do {
            $amount = $this->ask($message);
        } while (!$this->checkSumm($amount, $type));

        return $amount;
    }

    /**
     * @param $email
     * 
     * @return bool
     */
    public function checkIssetUser($email): bool
    {
        $this->user = User::whereEmail($email)->first();

        if (!$this->user) {
            $this->error('Sorry, "' . $email . '" was not found!');

            return false;
        }

        return true;
    }

    /**
     * @param  $amount
     * @param  string $type
     * 
     * @return bool
     */
    public function checkSumm($amount, $type): bool
    {
        if (!is_numeric($amount)) {
            $this->error('Sorry, the amount should be int or float!');

            return false;
        }

        if ($type == 'Withdraw' AND !$this->user->canWithdraw($amount)) {
            $this->error('Not enough money!');

            return false;
        }

        return true;
    }
}
