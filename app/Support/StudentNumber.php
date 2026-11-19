<?php

namespace App\Support;

/**
 * Student numbers look like COURSECODE/NNN/YEAR. Course codes used to be saved
 * with a slash on the end ("BT/"), which produced "BT//962/2026". Every place
 * that stores or looks up a number runs it through here, so a number never has
 * two slashes together, or a slash at either end.
 */
final class StudentNumber
{
    public static function clean(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }
        $number = trim($number);
        if (!str_contains($number, '/')) {
            return $number;
        }
        $number = preg_replace('#\s*/[\s/]*#', '/', $number); // "BT // 962" and "BT//962" => "BT/962"
        return trim($number, "/ \t");
    }

    /** Course code as used inside a number: "bt/ " => "BT". */
    public static function courseCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }
        return strtoupper(trim(preg_replace('#/+#', '', $code), " \t"));
    }

    /** The old double-slash spelling of a clean number ("BT/962/2026" => "BT//962/2026"), or null. */
    public static function oldSpelling(?string $number): ?string
    {
        return $number && substr_count($number, '/') === 2 ? preg_replace('#/#', '//', $number, 1) : null;
    }
}
