<?php

namespace PServerCore\Helper;

use DateTime;

/**
 * Class DateTimer
 * @package PServerCore\Helper
 */
class DateTimer
{
    /**
     * @param $timestamp
     *
     * @return DateTime
     */
    public static function getDateTime4TimeStamp($timestamp)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }

    /**
     * @param $timestamp
     *
     * @return int
     */
    public static function getZeroTimeStamp($timestamp)
    {
        return strtotime(date('Y-m-d', $timestamp));
    }

    /**
     * @param DateTime $beginDate
     * @param DateTime $endDate
     *
     * @return DateTime[]
     */
    public static function getDateRange4Period(DateTime $beginDate, DateTime $endDate)
    {
        $result = [];
        if ($beginDate < $endDate) {
            do {
                $result[] = clone $beginDate;
                $beginDate->modify('+1 day');
            } while ($beginDate <= $endDate);
        }

        return $result;
    }
} 