<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

final class Formatter
{
    public function identifier(string $id) : string
    {
        return \substr($id, \strrpos($id, '-') + 1);
    }

    /**
     * @param int|null $bytes
     */
    public function bytes($bytes, $decimals = 0) : string
    {
        if ($bytes === null) {
            return '-';
        }
        if ($bytes < 1024) {
            return \sprintf('%d bytes', $bytes);
        }
        return size_format($bytes, $decimals);
    }

    public function number($number, int $decimals = 0) : string
    {
        if ($number === null) {
            return '-';
        }
        return number_format_i18n($number, $decimals);
    }

    /**
     * @param \DateTimeInterface|null $date
     */
    public function date($date) : string
    {
        if ($date === null) {
            return '-';
        }
        return $date->format(__('Y/m/d g:i:s a'));
    }

    /**
     * @param \DateTimeInterface|null $date
     */
    public function shortDate($date) : string
    {
        if ($date === null) {
            return '-';
        }
        $timestamp = $date->getTimestamp();
        $difference = (new \DateTime())->getTimestamp() - $timestamp;
        if ($difference === 0) {
            return __('now', 'staatic');
        } elseif ($difference > 0 && $difference < DAY_IN_SECONDS) {
            return \sprintf(__('%s ago'), human_time_diff($timestamp));
        } else {
            return $date->format(__('Y/m/d'));
        }
    }

    /**
     * @param \DateTimeInterface|null $dateFrom
     * @param \DateTimeInterface|null $dateTo
     */
    public function difference($dateFrom, $dateTo) : string
    {
        if ($dateFrom === null || $dateTo === null) {
            return '-';
        }
        return human_time_diff($dateFrom->getTimestamp(), $dateTo->getTimestamp());
    }
}
