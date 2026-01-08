<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Increase upload limits for media files (videos can be up to 100MB)
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '110M');
ini_set('max_execution_time', '300'); // 5 minutes
ini_set('max_input_time', '300'); // 5 minutes

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.token' => \App\Http\Middleware\AuthenticateWithApiToken::class,
        ]);
        
        // Enable CORS for API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
