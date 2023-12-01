<?php

/**
 * Calculates the sum of calibration values from a file.
 *
 * @param string $filePath The path to the input file.
 * @return int The total sum of calibration values.
 */
function getCalibrationSum(string $filePath): int
{
    $totalSum = 0;
    $lines = file($filePath);

    foreach ($lines as $line) {
        $cleanLine = cleanLine($line);
        $calibrationValue = intval(substr($cleanLine, 0, 1) . substr($cleanLine, -1));
        $totalSum += $calibrationValue;
    }

    return $totalSum;
}

/**
 * Cleans a line by replacing spelled-out numbers with their numerical equivalents.
 *
 * @param string $line The line to clean.
 * @return int|null The cleaned line with only digits.
 */
function cleanLine(string $line): ?int
{
    $mappings = [
        'one' => 1, 'two' => 2, 'three' => 3, 'four' => 4, 'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8, 'nine' => 9
    ];

    foreach ($mappings as $string => $number) {
        //watch out ofr "eightwo" as the "t" is used for both and so a simple string replace will not work
        $line = str_replace($string,  substr($string, 0, 1) . $number . substr($string, -1, 1) , $line);
    }

    return preg_replace("/[^0-9]/", "", $line);
}

$filePath = 'input.txt';
echo "Total Calibration Sum: " . getCalibrationSum($filePath);