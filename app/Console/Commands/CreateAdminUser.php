<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $default_admin = config('app.admin');

        if (! User::where('email', '=', $default_admin['email'])->exists()) {
            User::create([
                'name' => $default_admin['name'],
                'email' => $default_admin['email'],
                'role' => 'admin',
                'password' => bcrypt($default_admin['password']),
            ]);
        }
    }
}
