<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Laravel\Sanctum\Sanctum;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // メール認証URLをフロントエンドのURLに変更
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());
            $signature = hash_hmac('sha256', "{$id}{$hash}", config('app.key'));
            $expires = now()->addMinutes(60)->timestamp;

            // フロントエンドのURL
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');

            return "{$frontendUrl}/email/verify/{$id}/{$hash}?expires={$expires}&signature={$signature}";
        });
    }
}
