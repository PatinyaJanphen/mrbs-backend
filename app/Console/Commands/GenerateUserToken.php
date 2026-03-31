<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenerateUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:token {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Sanctum token for a specific user ID for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $user = User::find($id);

        if (!$user) {
            $this->error("User with ID {$id} not found.");
            return 1;
        }

        // Revoke old tokens to keep it clean
        $user->tokens()->delete();
        
        $token = $user->createToken('test-token')->plainTextToken;

        $this->info("Token for {$user->name} ({$user->email}):");
        $this->line($token);
        
        $this->warn("\nCopy and paste this into your browser console:");
        $this->line("localStorage.setItem('mrbs_token', '{$token}'); localStorage.setItem('mrbs_user', JSON.stringify({name: '{$user->name}', email: '{$user->email}', role: {$user->role}})); location.reload();");

        return 0;
    }
}
