<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;

class EventService
{
    public function checkEnrollment(Event $event, ?User $user, string $action): ?string
    {
        if (!$user) {
            return 'You need to be logged in to ' . $action . ' in an event.';
        }

        if (!$event->isVisible()) {
            return 'This event is not currently available.';
        }

        if ($action === 'enroll' && $event->getParticipants()->contains($user)) {
            return 'You are already enrolled in this event.';
        }

        if ($action === 'unenroll' && !$event->getParticipants()->contains($user)) {
            return 'You are not enrolled in this event.';
        }

        $now = new \DateTime();

        if ($event->getEndDate() <= $now) {
            return 'This event has already ended. You cannot ' . $action . '.';
        } else if ($event->getStartDate() <= $now) {
            return 'This event has already started. You cannot ' . $action . '.';
        }

        return null;
    }
}
