<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TestAuth extends Command
{
    protected $signature = 'test:auth';
    protected $description = 'Run an internal test of register and authenticated /me endpoint';

    public function handle()
    {
        $this->info('Starting auth test...');

        $payload = [
            'name' => 'Test User',
            'username' => 'testuser' . rand(1000,9999),
            'email' => 'test' . rand(1000,9999) . '@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $request = Request::create('/api/v1/auth/register', 'POST', $payload, [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], json_encode($payload));
        $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);
        $response = $kernel->handle($request);
        $body = json_decode($response->getContent(), true);
        $this->line('Register response: ' . json_encode($body));

        if (empty($body['token'])) {
            $this->error('Register did not return token');
            return 1;
        }

        $token = $body['token'];
        $this->line('Got token (plain): ' . $token);

        $req2 = Request::create('/api/v1/me', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTP_ACCEPT' => 'application/json']);
        $resp2 = $kernel->handle($req2);
        $this->line('Authenticated /me status: ' . $resp2->getStatusCode());
        $this->line('Authenticated /me content: ' . $resp2->getContent());

        // Test my-posts endpoint
        $this->line("\n--- Testing My Posts Endpoint ---");
        $req3 = Request::create('/api/v1/my-posts', 'GET', [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $token, 'HTTP_ACCEPT' => 'application/json']);
        $resp3 = $kernel->handle($req3);
        $this->line('My posts status: ' . $resp3->getStatusCode());
        $this->line('My posts content: ' . $resp3->getContent());

        // Test public posts endpoint
        $this->line("\n--- Testing Public Post Detail ---");
        $req4 = Request::create('/api/v1/posts/15', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $resp4 = $kernel->handle($req4);
        $this->line('Post detail status: ' . $resp4->getStatusCode());
        $this->line('Post detail content: ' . $resp4->getContent());

        // Test user posts endpoint
        $this->line("\n--- Testing User Posts ---");
        $req5 = Request::create('/api/v1/users/johndoe/posts', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $resp5 = $kernel->handle($req5);
        $this->line('User posts status: ' . $resp5->getStatusCode());
        $this->line('User posts content: ' . $resp5->getContent());

        return 0;
    }
}
