<?php

namespace PicoBaz\JalaliFlow;

use DateTime;
use Exception;
use PicoBaz\JalaliFlow\JalaliEvent;

class JalaliFlow
{
    /**
     * Convert Gregorian date to Jalali.
     *
     * @param string $gregorianDate Gregorian date (Y-m-d format, e.g., '2025-05-14')
     * @param string $format Jalali date format (default: 'Y/m/d')
     * @return string Jalali date
     */
    public static function toJalali($gregorianDate, $format = 'Y/m/d')
    {
        try {
            $date = new DateTime($gregorianDate);
            $gregorianYear = (int) $date->format('Y');
            $gregorianMonth = (int) $date->format('m');
            $gregorianDay = (int) $date->format('d');

            // Convert to Julian Day
            $julianDay = gregoriantojd($gregorianMonth, $gregorianDay, $gregorianYear);

            // Convert Julian Day to Jalali
            $jalaliDate = self::julianToJalali($julianDay);

            // Format the output
            return self::formatJalaliDate($jalaliDate, $format);
        } catch (Exception $e) {
            return 'Invalid Gregorian date';
        }
    }

    /**
     * Convert Jalali date to Gregorian.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @return string Gregorian date (Y-m-d format)
     */
    public static function toGregorian($jalaliDate)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }

            // Parse Jalali date
            [$jalaliYear, $jalaliMonth, $jalaliDay] = array_map('intval', explode('/', $jalaliDate));

            // Convert to Julian Day
            $julianDay = self::jalaliToJulian($jalaliYear, $jalaliMonth, $jalaliDay);

            // Convert Julian Day to Gregorian
            [$gregorianMonth, $gregorianDay, $gregorianYear] = explode('/', jdtogregorian($julianDay));

            // Format as Y-m-d
            return sprintf('%04d-%02d-%02d', $gregorianYear, $gregorianMonth, $gregorianDay);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Check if a Jalali date is a holiday.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @return bool
     */
    public static function isHoliday($jalaliDate)
    {
        if (!self::validateJalaliDate($jalaliDate)) {
            return false;
        }

        $holidays = self::getHolidays();
        return isset($holidays[$jalaliDate]);
    }

    /**
     * Get list of holidays.
     *
     * @return array
     */
    public static function getHolidays()
    {
        return [
            '1404/01/01' => 'Norouz',
            '1404/01/02' => 'Norouz',
            // Add more holidays as needed
        ];
    }

    /**
     * Add days to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @param int $number Number of days to add (can be negative)
     * @return string New Jalali date
     */
    public static function addDay($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }

            // Convert Jalali to Gregorian
            $gregorianDate = self::toGregorian($jalaliDate);
            $date = new DateTime($gregorianDate);

            // Add days
            $date->modify("$number days");

            // Convert back to Jalali
            return self::toJalali($date->format('Y-m-d'));
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Subtract days from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @param int $number Number of days to subtract
     * @return string New Jalali date
     */
    public static function subDay($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addDay($jalaliDate, -$number);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Add weeks to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of weeks to add (can be negative)
     * @return string New Jalali date
     */
    public static function addWeek($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addDay($jalaliDate, $number * 7);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Subtract weeks from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of weeks to subtract
     * @return string New Jalali date
     */
    public static function subWeek($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addWeek($jalaliDate, -$number);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Add months to a Jalali date, considering variable month lengths.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of months to add (can be negative)
     * @return string New Jalali date
     */
    public static function addMonth($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }

            [$year, $month, $day] = array_map('intval', explode('/', $jalaliDate));

            // Calculate new month and year
            $totalMonths = $month + $number;
            $newYear = $year + floor(($totalMonths - 1) / 12);
            $newMonth = $totalMonths % 12;
            if ($newMonth <= 0) {
                $newMonth += 12;
                $newYear--;
            }

            // Adjust day if it exceeds the number of days in the new month
            $maxDays = self::getJalaliMonthDays($newMonth, $newYear);
            if ($day > $maxDays) {
                $day = $maxDays;
            }

            // Format the new Jalali date
            $newJalaliDate = sprintf('%04d/%02d/%02d', $newYear, $newMonth, $day);

            // Validate by converting to Gregorian and back
            $gregorianDate = self::toGregorian($newJalaliDate);
            return self::toJalali($gregorianDate);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Subtract months from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of months to subtract
     * @return string New Jalali date
     */
    public static function subMonth($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addMonth($jalaliDate, -$number);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Add years to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of years to add (can be negative)
     * @return string New Jalali date
     */
    public static function addYear($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addMonth($jalaliDate, $number * 12);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Subtract years from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of years to subtract
     * @return string New Jalali date
     */
    public static function subYear($jalaliDate, $number)
    {
        try {
            if (!self::validateJalaliDate($jalaliDate)) {
                return 'Invalid Jalali date';
            }
            return self::addYear($jalaliDate, -$number);
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Calculate the difference between two Jalali dates.
     *
     * @param string $startDate Jalali start date (Y/m/d format, e.g., '1404/02/24')
     * @param string $endDate Jalali end date (Y/m/d format, e.g., '1405/01/01')
     * @param string $unit Unit of difference ('day', 'week', 'month', 'year')
     * @return int|float|string Difference in the specified unit (absolute value)
     */
    public static function diff($startDate, $endDate, $unit = 'day')
    {
        try {
            if (!self::validateJalaliDate($startDate) || !self::validateJalaliDate($endDate)) {
                return 'Invalid Jalali date';
            }

            // Convert Jalali dates to Gregorian
            $startGregorian = self::toGregorian($startDate);
            $endGregorian = self::toGregorian($endDate);

            // Create DateTime objects
            $start = new DateTime($startGregorian);
            $end = new DateTime($endGregorian);

            // Calculate difference
            $interval = $start->diff($end);

            // Return difference based on unit
            switch (strtolower($unit)) {
                case 'day':
                    return abs($interval->days);
                case 'week':
                    return abs($interval->days / 7);
                case 'month':
                    $months = $interval->y * 12 + $interval->m;
                    if ($interval->d > 0) {
                        $daysInMonth = self::getJalaliMonthDays($interval->m + 1, $interval->y);
                        $months += $interval->d / $daysInMonth;
                    }
                    return abs($months);
                case 'year':
                    $months = $interval->y * 12 + $interval->m;
                    if ($interval->d > 0) {
                        $daysInMonth = self::getJalaliMonthDays($interval->m + 1, $interval->y);
                        $months += $interval->d / $daysInMonth;
                    }
                    return abs($months / 12);
                default:
                    return 'Invalid unit';
            }
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Create a new Jalali event.
     *
     * @param string $name Event name
     * @param string $frequency Frequency ('daily', 'weekly', 'monthly', 'yearly')
     * @param string $startDate Jalali start date (Y/m/d)
     * @param callable|string $action Action to execute (callable or class/method)
     * @return JalaliEvent|null
     */
    public static function createEvent($name, $frequency, $startDate, $action)
    {
        try {
            if (!self::validateJalaliDate($startDate)) {
                return null;
            }
            if (!in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'])) {
                return null;
            }

            // Serialize action if it's a callable
            $actionSerialized = is_callable($action) ? serialize($action) : $action;

            return JalaliEvent::create([
                'name' => $name,
                'frequency' => $frequency,
                'start_date' => $startDate,
                'next_run' => $startDate,
                'action' => $actionSerialized,
            ]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Calculate the next run date for an event.
     *
     * @param string $currentDate Jalali date (Y/m/d)
     * @param string $frequency Frequency ('daily', 'weekly', 'monthly', 'yearly')
     * @return string Next run date (Y/m/d)
     */
    public static function getNextRunDate($currentDate, $frequency)
    {
        try {
            if (!self::validateJalaliDate($currentDate)) {
                return 'Invalid Jalali date';
            }

            switch ($frequency) {
                case 'daily':
                    return self::addDay($currentDate, 1);
                case 'weekly':
                    return self::addWeek($currentDate, 1);
                case 'monthly':
                    return self::addMonth($currentDate, 1);
                case 'yearly':
                    return self::addYear($currentDate, 1);
                default:
                    return 'Invalid frequency';
            }
        } catch (Exception $e) {
            return 'Invalid Jalali date';
        }
    }

    /**
     * Validate a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @return bool True if valid, false otherwise
     */
    public static function validateJalaliDate($jalaliDate)
    {
        // Check format (YYYY/MM/DD)
        if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
            return false;
        }

        // Parse date
        [$year, $month, $day] = array_map('intval', explode('/', $jalaliDate));

        // Validate ranges
        if ($year < 1300 || $year > 1500 || $month < 1 || $month > 12 || $day < 1) {
            return false;
        }

        // Check day against max days in month
        $maxDays = self::getJalaliMonthDays($month, $year);
        return $day <= $maxDays;
    }

    /**
     * Convert Julian Day to Jalali date.
     *
     * @param int $julianDay
     * @return array [year, month, day]
     */
    private static function julianToJalali($julianDay)
    {
        $julianDay = floor($julianDay) + 0.5;

        $depoch = $julianDay - 2121446;

        // Calculate year
        $cycle = floor($depoch / 1029983);
        $cyear = $depoch % 1029983;
        $ycycle = floor($cyear / 36524);
        $aux1 = $cyear % 36524;
        $aux2 = floor($aux1 / 365);
        $year = $cycle * 2820 + $ycycle * 128 + $aux2 + 474;

        // Calculate day and month
        $yday = $julianDay - gregoriantojd(1, 1, $year - 474) + 1;
        $month = $yday <= 186 ? ceil($yday / 31) : ceil(($yday - 6) / 30);
        $day = $yday - ($month - 1) * 31 - floor($month / 7) * ($month > 7 ? 1 : 0);

        return [(int) $year, (int) $month, (int) $day];
    }

    /**
     * Convert Jalali date to Julian Day.
     *
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int
     */
    private static function jalaliToJulian($year, $month, $day)
    {
        $depoch = $year - 474;
        $cycle = floor($depoch / 2820);
        $cyear = $depoch % 2820;
        $ycycle = floor($cyear / 128);
        $aux1 = $cyear % 128;
        $yday = floor(($aux1 * 365 + floor($aux1 / 4) + 1 + ($month - 1) * 31 - floor($month / 7) * ($month > 7 ? 1 : 0) + $day - 1));
        $julianDay = $cycle * 1029983 + $ycycle * 46751 + $yday + 2121446;

        return $julianDay;
    }

    /**
     * Format Jalali date according to the specified format.
     *
     * @param array $jalaliDate [year, month, day]
     * @param string $format
     * @return string
     */
    private static function formatJalaliDate($jalaliDate, $format)
    {
        [$year, $month, $day] = $jalaliDate;
        $format = str_replace(['Y', 'm', 'd'], ['%04d', '%02d', '%02d'], $format);
        return sprintf($format, $year, $month, $day);
    }

    /**
     * Get number of days in a Jalali month.
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    private static function getJalaliMonthDays($month, $year)
    {
        if ($month >= 1 && $month <= 6) {
            return 31; // Months 1-6 have 31 days
        }
        if ($month >= 7 && $month <= 11) {
            return 30; // Months 7-11 have 30 days
        }
        if ($month == 12) {
            return self::isJalaliLeapYear($year) ? 30 : 29; // Esfand: 29 or 30 in leap year
        }
        return 0; // Invalid month
    }

    /**
     * Check if a Jalali year is a leap year.
     *
     * @param int $year
     * @return bool
     */
    private static function isJalaliLeapYear($year)
    {
        // Algorithm for Jalali leap year
        $remainder = fmod($year * 8 + 21, 33);
        return $remainder < 8;
    }
}