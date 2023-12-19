<?php

const FILEPATH = 'input.txt';

/**
 * Calculates the hash code for a given string.
 *
 * @param string $string The input string for which the hash code needs to be calculated.
 * @return int The hash code for the given string.
 */
function getHash(string $string): int {
    $result = 0;
    foreach (str_split($string) as $char) {
        $result += ord($char);
        $result *= 17;
        $result %= 256;
    }
    return $result;
}

/**
 * Initializes boxes with lenses based on the contents of a file.
 *
 * @param string $filePath The path to the file containing the lenses data.
 * @return array An array representing the initialized boxes. Each index of the array corresponds to a hash code,
 *               and the value is an associative array of lenses with their labels as key and focal lengths as value.
 */
function initializeBoxes(string $filePath): array {
    $input = file_get_contents($filePath);
    $input = explode(',', $input);
    $boxes = array_fill(0, 256, []);

    foreach ($input as $data) {
        preg_match('/^(.+)([=-])(.*)$/', $data, $matches);
        $label = $matches[1];
        $hash = getHash($label);
        $operation = $matches[2];
        $focalLength = $matches[3] ?? null;

        if ($operation === '-') {
            // Remove lens if present
            unset($boxes[$hash][$label]);
        } else {
            // Add or update lens
            $boxes[$hash][$label] = (int)$focalLength;
        }
    }

    return $boxes;
}

/**
 * Calculates the focusing power based on the given boxes.
 *
 * @param array $boxes An array containing the boxes and their focal lengths.
 *
 * @return int The calculated focusing power.
 */
function calculateFocusingPower(array $boxes): int {
    $totalPower = 0;

    foreach ($boxes as $boxIndex => $box) {
        if (empty($box)) continue;

        $slotIndex = 0;
        foreach ($box as $focalLength) {
            $totalPower += ($boxIndex + 1) * (++$slotIndex) * $focalLength;
        }
    }

    return $totalPower;
}

$boxes = initializeBoxes(FILEPATH);
$totalFocusingPower = calculateFocusingPower($boxes);

echo "Total Focusing Power: " . $totalFocusingPower . PHP_EOL;