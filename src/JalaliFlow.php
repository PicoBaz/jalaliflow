<?php

namespace PicoBaz\JalaliFlow;

class JalaliFlow
{
    protected $holidays = [
        '1404/01/01' => 'نوروز',
        '1404/01/02' => 'نوروز',
       
    ];

    public function toJalali($gregorianDate, $format = null)
    {
        $format = $format ?? config('jalalisync.date_format', 'Y/m/d');
        $date = \DateTime::createFromFormat('Y-m-d', $gregorianDate);

        if (!$date) {
            return null;
        }

        
        $jd = gregoriantojd($date->format('m'), $date->format('d'), $date->format('Y'));
        $jalali = $this->jdToJalali($jd);

        return sprintf($format, $jalali[0], $jalali[1], $jalali[2]);
    }

    public function toGregorian($jalaliDate)
    {
        [$year, $month, $day] = explode('/', $jalaliDate);
        $jd = $this->jalaliToJd($year, $month, $day);
        return jdtogregorian($jd);
    }

    public function isHoliday($jalaliDate)
    {
        return isset($this->holidays[$jalaliDate]);
    }

    public function addEvent($event)
    {
       
        return [
            'title' => $event['title'],
            'date' => $event['date'],
            'repeat' => $event['repeat'] ?? 'none',
        ];
    }

   
    protected function jdToJalali($jd)
    {
        $gy = 1600;
        $gm = 0;
        $gd = $jd - 1948320.5 - 79;

        $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);
        for ($i = 0; $i < $gy; ++$i) {
            $g_day_no += 365;
            if ($i % 4 == 0) {
                $g_day_no++;
            }
        }

        $j_day_no = $gd - $g_day_no;
        $j_np = floor($j_day_no / 12053);
        $j_day_no %= 12053;

        $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);
        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += floor(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }

        $days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $jm = 0;
        $jd = $j_day_no;

        for ($i = 0; $i < 12 && $jd >= $days_in_month[$i]; ++$i) {
            $jd -= $days_in_month[$i];
            $jm++;
        }

        $jd++;
        return [$jy, $jm + 1, $jd];
    }

    protected function jalaliToJd($year, $month, $day)
    {
        $gy = $year - 979;
        $days = 365 * $gy + floor($gy / 33) * 8 + floor(($gy % 33 + 3) / 4) + 78 + $day;

        for ($i = 1; $i < $month; ++$i) {
            $days += ($i <= 6) ? 31 : (($i == 12 && !$this->isLeapYear($year)) ? 29 : 30);
        }

        return $days + 1948320.5;
    }

    protected function isLeapYear($year)
    {
        $mod = $year % 33;
        return in_array($mod, [1, 5, 9, 13, 17, 22, 26, 30]);
    }
}
