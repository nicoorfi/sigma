<?php

namespace App\Traits;

use App\Notifications\ConfirmSubscription;

trait MustConfirmSubscription
{
    /**
     * @return bool
     */
    public function subscriptionConfirmed(): bool
    {
        return $this->confirmed !== null;
    }

    /**
     * @return bool
     */
    public function confirmSubscription(): void
    {
        $this->forceFill(['confirmed' => true])->save();
    }

    /**
     * @return void
     */
    public function sendConfirmationEmailNotification(): void
    {
        $this->notify(new ConfirmSubscription);
    }
}