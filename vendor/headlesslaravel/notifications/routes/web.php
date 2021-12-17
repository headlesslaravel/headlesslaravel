<?php

use \HeadlessLaravel\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'verified'])
    ->group(function() {
        Route::get('notifications', [NotificationController::class, 'all']);
        Route::get('notifications/unread', [NotificationController::class, 'unread']);
        Route::get('notifications/read', [NotificationController::class, 'read']);
        Route::get('notifications/count', [NotificationController::class, 'count']);
        Route::post('notifications/clear', [NotificationController::class, 'clear']);
        Route::post('notifications/{notification}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);
    });
