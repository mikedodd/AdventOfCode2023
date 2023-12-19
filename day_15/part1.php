<?php

const FILEPATH = 'input.txt';

/**
 * Calculates a hash value for a given string using a custom algorithm.
 *
 * @param string $str The input string to calculate the hash value for.
 *
 * @return int The calculated hash value.
 */
function hashAlgorithm(string $str): int
{
    $currentValue = 0;
    for ($i = 0; $i < strlen($str); $i++) {
        $asciiCode = ord($str[$i]);
        $currentValue += $asciiCode;
        $currentValue *= 17;
        $currentValue %= 256;
    }
    return $currentValue;
}

/**
 * Calculates the sum of the hash results obtained by applying a hash algorithm to each step in the initialization sequence.
 *
 * @param string $filePath The path to the file containing the initialization sequence.
 * @return int The sum of the hash results.
 */
function sumHashResults(string $filePath): int
{
    $initializationSequence = file_get_contents($filePath);
    $steps = explode(',', $initializationSequence);
    $sum = 0;
    foreach ($steps as $step) {
        $sum += hashAlgorithm($step);
    }
    return $sum;
}

echo "Sum of HASH results: " . sumHashResults(FILEPATH);