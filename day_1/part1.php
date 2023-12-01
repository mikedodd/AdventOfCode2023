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
        $cleanLine = preg_replace("/[^0-9]/", "", $line);
        $calibrationValue = intval(substr($cleanLine, 0, 1) . substr($cleanLine, -1));
        $totalSum += $calibrationValue;
    }

    return $totalSum;
}

$filePath = 'input.txt';
echo "Total Calibration Sum: " . getCalibrationSum($filePath);