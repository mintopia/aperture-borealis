<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use function Laravel\Prompts\text;

class AddUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borealis:add-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user to Borealis to allow them to authenticate';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nickname = text(
            label: 'Nickname',
            required: true,
            validate: 'string|min:2',
            hint: 'The nickname for the user'
        );
        $email = text(
            label: 'Email Address',
            required: true,
            validate: 'email|unique:users,email',
            hint: 'The email address for the user'
        );
        $user = new User();
        $user->nickname = $nickname;
        $user->email = $email;
        $user->save();
        $this->info("Added {$user}");
    }
}
