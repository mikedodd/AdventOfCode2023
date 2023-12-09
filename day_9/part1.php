<?php

/**
 * Parses the contents of a file into an array of lines.
 *
 * @param string $filePath The path to the file.
 * @return array The array of lines in the file.
 *
 * @throws InvalidArgumentException if the $filePath parameter is not a string.
 */
function parseInput(string $filePath): array
{
    return file($filePath, FILE_IGNORE_NEW_LINES);
}

/**
 * Extrapolates the next values in a series based on historical values.
 *
 * @param array $lines The array of historical values.
 *         Each line is a string of space-separated integers.
 *         Example: ['1 2 3', '4 5 6'].
 *
 * @return int The sum of the extrapolated next values in the series.
 *
 * @throws InvalidArgumentException if the $lines parameter is not an array.
 */
function extrapolate(array $lines): int
{
    $histories = array_map(function ($line) {
        return array_map('intval', explode(' ', $line));
    }, $lines);

    $nextValues = [];

    foreach ($histories as $history) {
        $sequence = $history;
        $lastValues = [];

        while (count($sequence) > 1) {
            $newSequence = [];
            for ($i = 0; $i < count($sequence) - 1; $i++) {
                $newSequence[] = $sequence[$i + 1] - $sequence[$i];
            }
            $sequence = $newSequence;

            if (count($sequence) > 0) {
                array_unshift($lastValues, end($sequence));
            }
        }

        $nextValue = array_sum($lastValues) + end($history);
        $nextValues[] = $nextValue;
    }

    return array_sum($nextValues);
}

$filePath = 'input.txt';
$lines = parseInput($filePath);
echo "Sum of these extrapolated values: " . extrapolate($lines) . "\n";
