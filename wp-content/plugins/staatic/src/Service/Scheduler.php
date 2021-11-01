<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

final class Scheduler
{
    public function getSchedules() : array
    {
        return \array_map(function ($schedule) {
            return [
                'label' => $schedule['display'],
                'interval' => $schedule['interval']
            ];
        }, wp_get_schedules());
    }

    public function isScheduled(string $event) : bool
    {
        //!COMPAT: Introduced in WordPress 5.1.0 - replace with something else...
        // https://developer.wordpress.org/reference/functions/wp_get_scheduled_event/
        $nextEvent = Polyfill::wp_get_scheduled_event($event);
        return $nextEvent !== \false;
    }

    /**
     * @return void
     */
    public function unschedule(string $event)
    {
        $nextEventTimestamp = wp_next_scheduled($event);
        if (!\is_int($nextEventTimestamp)) {
            throw new \RuntimeException(\sprintf('Unable to unschedule event "%s"; event does not exist', $event));
        }
        $result = Polyfill::wp_unschedule_event($nextEventTimestamp, $event);
        if ($result !== \true) {
            throw new \RuntimeException(\sprintf(
                'Unable to unschedule event "%s" scheduled at timestamp "%d"; unknown error',
                $event,
                $nextEventTimestamp
            ));
        }
    }

    /**
     * @return void
     */
    public function schedule(string $event, string $schedule)
    {
        $result = Polyfill::wp_schedule_event(\time(), $schedule, $event, []);
        if ($result !== \true) {
            throw new \RuntimeException(\sprintf(
                'Unable to schedule event "%s" with schedule "%s"; unknown error',
                $event,
                $schedule
            ));
        }
    }
}
