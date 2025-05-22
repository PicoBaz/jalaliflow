<?php

namespace PicoBaz\JalaliFlow;

use DateTime;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use PicoBaz\JalaliFlow\JalaliEvent;

class JalaliFlow
{
    /**
     * Convert Gregorian date to Jalali with JDF-inspired formatting.
     *
     * @param string|int $gregorianDate Gregorian date (Y-m-d format, e.g., '2025-05-14') or Unix timestamp
     * @param string $format Jalali date format (e.g., 'Y/m/d', 'l j F Y')
     * @param string $timezone Timezone (default: 'Asia/Tehran')
     * @param string $lang Language for numbers ('fa' for Persian, 'en' for English)
     * @return string Formatted Jalali date
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function toJalali($gregorianDate, string $format = 'Y/m/d', string $timezone = 'Asia/Tehran', string $lang = 'fa'): string
    {
        if ($timezone !== 'local') {
            date_default_timezone_set($timezone ?: 'Asia/Tehran');
        }

        // Handle timestamp or date string
        $ts = is_numeric($gregorianDate) ? (int)$gregorianDate : ($gregorianDate ? strtotime($gregorianDate) : time());
        if ($ts === false || $ts < -62135596800) { // Before 0001-01-01
            throw new InvalidArgumentException('Invalid Gregorian date or timestamp. Use Y-m-d or Unix timestamp.');
        }

        $date = explode('_', date('H_i_j_n_O_P_s_w_Y', $ts));
        [$j_y, $j_m, $j_d] = self::gregorianToJalali((int)$date[8], (int)$date[3], (int)$date[2]);

        // Calculate day of year and leap year
        $doy = $j_m < 7 ? (($j_m - 1) * 31) + $j_d - 1 : (($j_m - 7) * 30) + $j_d + 185;
        $kab = ((((($j_y + 12) % 33) % 4) == 1) ? 1 : 0);

        if ($j_y < 1 || $j_m < 1 || $j_m > 12 || $j_d < 1 || $j_d > self::getJalaliMonthDays($j_m, $j_y)) {
            throw new RuntimeException('Invalid Jalali date calculated.');
        }

        $out = '';
        $sl = strlen($format);
        for ($i = 0; $i < $sl; $i++) {
            $sub = substr($format, $i, 1);
            if ($sub === '\\') {
                $out .= substr($format, ++$i, 1);
                continue;
            }

            switch ($sub) {
                case 'a':
                    $out .= (int)$date[0] < 12 ? 'ق.ظ' : 'ب.ظ';
                    break;
                case 'A':
                    $out .= (int)$date[0] < 12 ? 'قبل از ظهر' : 'بعد از ظهر';
                    break;
                case 'b':
                    $out .= (int)($j_m / 3.1) + 1;
                    break;
                case 'c':
                    $out .= "$j_y/$j_m/$j_d ، {$date[0]}:{$date[1]}:{$date[6]} {$date[5]}";
                    break;
                case 'C':
                    $out .= (int)(($j_y + 99) / 100);
                    break;
                case 'd':
                    $out .= $j_d < 10 ? '0' . $j_d : $j_d;
                    break;
                case 'D':
                    $out .= self::jdateWords(['kh' => (int)$date[7]], ' ');
                    break;
                case 'f':
                    $out .= self::jdateWords(['ff' => $j_m], ' ');
                    break;
                case 'F':
                    $out .= self::jdateWords(['mm' => $j_m], ' ');
                    break;
                case 'H':
                    $out .= $date[0];
                    break;
                case 'i':
                    $out .= $date[1];
                    break;
                case 'j':
                    $out .= $j_d;
                    break;
                case 'J':
                    $out .= self::jdateWords(['rr' => $j_d], ' ');
                    break;
                case 'k':
                    $out .= self::trNum(100 - (int)($doy / ($kab + 365.24) * 1000) / 10, $lang);
                    break;
                case 'K':
                    $out .= self::trNum((int)($doy / ($kab + 365.24) * 1000) / 10, $lang);
                    break;
                case 'l':
                    $out .= self::jdateWords(['rh' => (int)$date[7]], ' ');
                    break;
                case 'L':
                    $out .= $kab;
                    break;
                case 'm':
                    $out .= $j_m > 9 ? $j_m : '0' . $j_m;
                    break;
                case 'M':
                    $out .= self::jdateWords(['km' => $j_m], ' ');
                    break;
                case 'n':
                    $out .= $j_m;
                    break;
                case 's':
                    $out .= $date[6];
                    break;
                case 'S':
                    $out .= 'ام';
                    break;
                case 't':
                    $out .= ($j_m != 12) ? (31 - (int)($j_m / 6.5)) : ($kab + 29);
                    break;
                case 'w':
                    $out .= (int)$date[7] == 6 ? 0 : (int)$date[7] + 1;
                    break;
                case 'Y':
                    $out .= $j_y;
                    break;
                case 'y':
                    $out .= substr($j_y, 2, 2);
                    break;
                case 'z':
                    $out .= $doy;
                    break;
                default:
                    $out .= $sub;
            }
        }

        return $lang === 'fa' ? self::trNum($out, 'fa') : $out;
    }

    /**
     * Convert Jalali date to Gregorian.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @return string Gregorian date (Y-m-d format)
     * @throws InvalidArgumentException
     */
    public static function toGregorian(string $jalaliDate): string
    {
        if (!self::validateJalaliDate($jalaliDate)) {
            throw new InvalidArgumentException('Invalid Jalali date. Use Y/m/d format.');
        }

        [$j_y, $j_m, $j_d] = array_map('intval', explode('/', $jalaliDate));
        [$g_y, $g_m, $g_d] = self::jalaliToGregorian($j_y, $j_m, $j_d);

        return sprintf('%04d-%02d-%02d', $g_y, $g_m, $g_d);
    }

    /**
     * Check if a Jalali date is a holiday.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @return bool
     */
    public static function isHoliday(string $jalaliDate): bool
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
    public static function getHolidays(): array
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
     * @throws InvalidArgumentException
     */
    public static function addDay(string $jalaliDate, int $number): string
    {
        if (!self::validateJalaliDate($jalaliDate)) {
            throw new InvalidArgumentException('Invalid Jalali date.');
        }

        $gregorianDate = self::toGregorian($jalaliDate);
        $date = new DateTime($gregorianDate);
        $date->modify("$number days");
        return self::toJalali($date->format('Y-m-d'));
    }

    /**
     * Subtract days from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of days to subtract
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function subDay(string $jalaliDate, int $number): string
    {
        return self::addDay($jalaliDate, -$number);
    }

    /**
     * Add weeks to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of weeks to add (can be negative)
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function addWeek(string $jalaliDate, int $number): string
    {
        return self::addDay($jalaliDate, $number * 7);
    }

    /**
     * Subtract weeks from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of weeks to subtract
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function subWeek(string $jalaliDate, int $number): string
    {
        return self::addWeek($jalaliDate, -$number);
    }

    /**
     * Add months to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of months to add (can be negative)
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function addMonth(string $jalaliDate, int $number): string
    {
        if (!self::validateJalaliDate($jalaliDate)) {
            throw new InvalidArgumentException('Invalid Jalali date.');
        }

        [$year, $month, $day] = array_map('intval', explode('/', $jalaliDate));
        $totalMonths = $month + $number;
        $newYear = $year + floor(($totalMonths - 1) / 12);
        $newMonth = $totalMonths % 12;
        if ($newMonth <= 0) {
            $newMonth += 12;
            $newYear--;
        }

        // Handle edge cases for days (e.g., 29th of Esfand in non-leap year)
        $maxDays = self::getJalaliMonthDays($newMonth, $newYear);
        if ($day > $maxDays) {
            $day = $maxDays;
        }

        // Validate the new date
        $newJalaliDate = sprintf('%04d/%02d/%02d', $newYear, $newMonth, $day);
        if (!self::validateJalaliDate($newJalaliDate)) {
            throw new RuntimeException('Calculated Jalali date is invalid.');
        }

        return $newJalaliDate;
    }

    /**
     * Subtract months from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of months to subtract
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function subMonth(string $jalaliDate, int $number): string
    {
        return self::addMonth($jalaliDate, -$number);
    }

    /**
     * Add years to a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of years to add (can be negative)
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function addYear(string $jalaliDate, int $number): string
    {
        return self::addMonth($jalaliDate, $number * 12);
    }

    /**
     * Subtract years from a Jalali date.
     *
     * @param string $jalaliDate Jalali date (Y/m/d format)
     * @param int $number Number of years to subtract
     * @return string New Jalali date
     * @throws InvalidArgumentException
     */
    public static function subYear(string $jalaliDate, int $number): string
    {
        return self::addYear($jalaliDate, -$number);
    }

    /**
     * Calculate the difference between two Jalali dates.
     *
     * @param string $startDate Jalali start date (Y/m/d format)
     * @param string $endDate Jalali end date (Y/m/d format)
     * @param string $unit Unit of difference ('day', 'week', 'month', 'year')
     * @return int|float Difference in the specified unit (absolute value)
     * @throws InvalidArgumentException
     */
    public static function diff(string $startDate, string $endDate, string $unit = 'day')
    {
        if (!self::validateJalaliDate($startDate) || !self::validateJalaliDate($endDate)) {
            throw new InvalidArgumentException('Invalid Jalali date.');
        }

        $startGregorian = self::toGregorian($startDate);
        $endGregorian = self::toGregorian($endDate);

        $start = new DateTime($startGregorian);
        $end = new DateTime($endGregorian);

        $interval = $start->diff($end);

        switch (strtolower($unit)) {
            case 'day':
                return abs($interval->days);
            case 'week':
                return abs($interval->days / 7);
            case 'month':
                $months = $interval->y * 12 + $interval->m + ($interval->d / 30);
                return abs($months);
            case 'year':
                $years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
                return abs($years);
            default:
                throw new InvalidArgumentException('Invalid unit.');
        }
    }

    /**
     * Create a new Jalali event.
     *
     * @param string $name Event name
     * @param string $frequency Frequency ('daily', 'weekly', 'monthly', 'yearly')
     * @param string $startDate Jalali start date (Y/m/d)
     * @param callable|string $action Action to execute
     * @return JalaliEvent|null
     */
    public static function createEvent(string $name, string $frequency, string $startDate, $action): ?JalaliEvent
    {
        if (!self::validateJalaliDate($startDate) || !in_array($frequency, ['daily', 'weekly', 'monthly', 'yearly'])) {
            return null;
        }

        $actionSerialized = is_callable($action) ? serialize($action) : $action;

        return JalaliEvent::create([
            'name' => $name,
            'frequency' => $frequency,
            'start_date' => $startDate,
            'next_run' => $startDate,
            'action' => $actionSerialized,
        ]);
    }

    /**
     * Calculate the next run date for an event.
     *
     * @param string $currentDate Jalali date (Y/m/d)
     * @param string $frequency Frequency ('daily', 'weekly', 'monthly', 'yearly')
     * @return string Next run date (Y/m/d)
     * @throws InvalidArgumentException
     */
    public static function getNextRunDate(string $currentDate, string $frequency): string
    {
        if (!self::validateJalaliDate($currentDate)) {
            throw new InvalidArgumentException('Invalid Jalali date.');
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
                throw new InvalidArgumentException('Invalid frequency.');
        }
    }

    /**
     * Validate a Jalali date (inspired by JDF's jcheckdate).
     *
     * @param string $jalaliDate Jalali date (Y/m/d format, e.g., '1404/02/24')
     * @return bool True if valid, false otherwise
     */
    public static function validateJalaliDate(string $jalaliDate): bool
    {
        if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
            return false;
        }

        [$jy, $jm, $jd] = array_map('intval', explode('/', $jalaliDate));
        if ($jy < 1300 || $jy > 1500 || $jm < 1 || $jm > 12 || $jd < 1) {
            return false;
        }

        $l_d = ($jm == 12 && ((($jy + 12) % 33) % 4) != 1) ? 29 : (31 - (int)($jm / 6.5));
        return $jd <= $l_d;
    }

    /**
     * Convert Gregorian to Jalali (JDF algorithm).
     *
     * @param int $gy Year
     * @param int $gm Month
     * @param int $gd Day
     * @return array [year, month, day]
     * @throws InvalidArgumentException
     */
    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        if (!checkdate($gm, $gd, $gy) || $gy < 622) {
            throw new InvalidArgumentException('Invalid Gregorian date. Year must be >= 622.');
        }

        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        $gy2 = $gm > 2 ? $gy + 1 : $gy;
        $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100)) + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
        $jy = -1595 + (33 * ((int)($days / 12053)));
        $days %= 12053;
        $jy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $jy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        if ($days < 186) {
            $jm = 1 + (int)($days / 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + (int)(($days - 186) / 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [(int)$jy, (int)$jm, (int)$jd];
    }

    /**
     * Convert Jalali to Gregorian (JDF algorithm).
     *
     * @param int $jy Year
     * @param int $jm Month
     * @param int $jd Day
     * @return array [year, month, day]
     * @throws InvalidArgumentException
     */
    private static function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        if ($jy < 1 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > self::getJalaliMonthDays($jm, $jy)) {
            throw new InvalidArgumentException('Invalid Jalali date.');
        }

        $jy += 1595;
        $days = -355668 + (365 * $jy) + (((int)($jy / 33)) * 8) + ((int)((($jy % 33) + 3) / 4)) + $jd + ($jm < 7 ? ($jm - 1) * 31 : (($jm - 7) * 30) + 186);
        $gy = 400 * ((int)($days / 146097));
        $days %= 146097;
        if ($days > 36524) {
            $gy += 100 * ((int)(--$days / 36524));
            $days %= 36524;
            if ($days >= 365) $days++;
        }
        $gy += 4 * ((int)($days / 1461));
        $days %= 1461;
        if ($days > 365) {
            $gy += (int)(($days - 1) / 365);
            $days = ($days - 1) % 365;
        }
        $gd = $days + 1;
        $sal_a = [0, 31, (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        for ($gm = 0; $gm < 13 && $gd > $sal_a[$gm]; $gm++) {
            $gd -= $sal_a[$gm];
        }

        return [(int)$gy, (int)$gm, (int)$gd];
    }

    /**
     * Convert numbers to Persian or English (JDF's tr_num).
     *
     * @param string $str Input string
     * @param string $mod Mode ('fa' for Persian, 'en' for English)
     * @return string Converted string
     */
    private static function trNum(string $str, string $mod = 'en'): string
    {
        $num_a = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.'];
        $key_a = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٫'];
        return $mod === 'fa' ? str_replace($num_a, $key_a, $str) : str_replace($key_a, $num_a, $str);
    }

    /**
     * Convert numbers and months to Persian words (JDF's jdate_words).
     *
     * @param array $array Key-value pairs (e.g., ['mm' => 1])
     * @param string $mod Separator
     * @return string|array Converted words
     */
    private static function jdateWords(array $array, string $mod = ''): string|array
    {
        foreach ($array as $type => $num) {
            $num = (int)$num;
            switch ($type) {
                case 'mm':
                    $key = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
                    $array[$type] = $key[$num - 1];
                    break;
                case 'rr':
                    $key = [
                        'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه', 'ده',
                        'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده',
                        'بیست', 'بیست و یک', 'بیست و دو', 'بیست و سه', 'بیست و چهار', 'بیست و پنج',
                        'بیست و شش', 'بیست و هفت', 'بیست و هشت', 'بیست و نه', 'سی', 'سی و یک'
                    ];
                    $array[$type] = $key[$num - 1];
                    break;
                case 'rh':
                    $key = ['یکشنبه', 'دوشنبه', 'سه شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];
                    $array[$type] = $key[$num];
                    break;
                case 'ff':
                    $key = ['بهار', 'تابستان', 'پاییز', 'زمستان'];
                    $array[$type] = $key[(int)($num / 3.1)];
                    break;
                case 'km':
                    $key = ['فر', 'ار', 'خر', 'تی‍', 'مر', 'شه‍', 'مه‍', 'آب‍', 'آذ', 'دی', 'به‍', 'اس‍'];
                    $array[$type] = $key[$num - 1];
                    break;
                case 'kh':
                    $key = ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'];
                    $array[$type] = $key[$num];
                    break;
                default:
                    $array[$type] = $num;
            }
        }
        return $mod === '' ? $array : implode($mod, $array);
    }

    /**
     * Get number of days in a Jalali month.
     *
     * @param int $month
     * @param int $year
     * @return int
     */
    private static function getJalaliMonthDays(int $month, int $year): int
    {
        if ($month >= 1 && $month <= 6) {
            return 31;
        }
        if ($month >= 7 && $month <= 11) {
            return 30;
        }
        if ($month == 12) {
            return ((($year + 12) % 33) % 4) == 1 ? 30 : 29;
        }
        return 0;
    }

    /**
     * Check if a Jalali year is a leap year.
     *
     * @param int $year
     * @return bool
     */
    private static function isJalaliLeapYear(int $year): bool
    {
        return ((($year + 12) % 33) % 4) == 1;
    }
}