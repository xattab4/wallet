<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user command';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {   
        do {
            $name = $this->ask('Name');
            $email = $this->askUniqueEmail('E-mail');
            $password = $this->secret('Password');
        } while (!$this->confirm("Create user {$name} <{$email}>?", true));

        $user = User::forceCreate([
            'name' => $name, 
            'email' => $email, 
            'password' => Hash::make($password)
        ]);

        $this->info("Created new user #{$user->id}");

        return 1;
    }

    /**
     * @param      $message
     * @param null $default
     * 
     * @return string
     */
    protected function askUniqueEmail($message, $default = null): string
    {
        do {
            $email = $this->ask($message, $default);
        } while (!$this->checkEmailIsValid($email) || !$this->checkEmailIsUnique($email));

        return $email;
    }

    /**
     * @param $email
     * 
     * @return bool
     */
    protected function checkEmailIsValid($email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Sorry, "' . $email . '" is not a valid email address!');

            return false;
        }

        return true;
    }

    /**
     * @param $email
     * 
     * @return bool
     */
    public function checkEmailIsUnique($email): bool
    {
        if ($existingUser = User::whereEmail($email)->first()) {
            $this->error('Sorry, "' . $existingUser->email . '" is already in use by ' . $existingUser->name . '!');

            return false;
        }

        return true;
    }
}
