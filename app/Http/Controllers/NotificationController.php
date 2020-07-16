<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Intl\Exception\NotImplementedException;

class NotificationController extends Controller
{
    public function index()
    {
        $beforeOneWeek = Carbon::now()->subWeek()->toDateString();

        return Auth::user()->notifications->where('created_at', '>', $beforeOneWeek)->sortByDesc('created_at')->toArray();
    }

    public function show($id)
    {
        return Auth::user()->notifications->where('id', $id)->first();
    }

    public function update($id)
    {
        Auth::user()->notifications->firstWhere('id', $id)->markAsRead();
    }
}
