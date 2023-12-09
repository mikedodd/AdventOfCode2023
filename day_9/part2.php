<?php

/**
 * Parses the input file and returns an array of strings.
 *
 * The method reads the content of the file specified by the $filePath parameter,
 * ignoring the new line characters. It then returns an array, where each element
 * represents a line from the file.
 *
 * @param string $filePath The path to the input file.
 * @return array An array of strings, where each string represents a line from the file.
 */
function parseInput(string $filePath): array
{
    return file($filePath, FILE_IGNORE_NEW_LINES);
}

/**
 * Extrapolates the next values in a sequence based on the given lines.
 *
 * Each line in the $lines array should contain integers separated by spaces, representing a sequence of values.
 * The method calculates the next value in each sequence by finding the differences between consecutive values,
 * summing the last values obtained, and adding it to the last value in the sequence.
 * The method returns the sum of all the next values calculated.
 *
 * @param array $lines An array of strings, where each string represents a line of integers separated by spaces.
 * @return int The sum of all the next values calculated in the sequences.
 */
function extrapolate(array $lines): int
{
    $histories = array_map(function ($line) {
        return array_map('intval', explode(' ', $line));
    }, $lines);

    $histories = array_map('array_reverse', $histories);

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

