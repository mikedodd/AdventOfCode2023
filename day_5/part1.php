<?php

/**
 * Parses a line to get the mapping details.
 *
 * @param string $line The line from the file containing mapping information.
 * @return array An array containing the start and end of the source and destination ranges, and the length of the range.
 */
function parseMapping(string $line): array {
    return explode(' ', trim($line));
}

/**
 * Converts a number from one category to another based on provided mappings.
 *
 * @param int $number The number to be converted.
 * @param array $mappings The mappings that define how numbers are converted between categories.
 * @return int The converted number, or the original number if no conversion is applicable.
 */
function convert(int $number, array $mappings): int {
    foreach ($mappings as $mapping) {
        list($destStart, $sourceStart, $length) = $mapping;
        if ($number >= $sourceStart && $number < $sourceStart + $length) {
            return $destStart + ($number - $sourceStart);
        }
    }
    return $number;
}

/**
 * Determines the lowest location number among all seeds.
 *
 * @param array $seeds Array of seed numbers.
 * @param array $maps Array of maps for category conversions.
 * @return int|null The lowest location number, or null if no seeds are provided.
 */
function getLowestLocation(array $seeds, array $maps): ?int {
    $lowestLocation = null;

    foreach ($seeds as $seed) {
        $currentNumber = $seed;
        foreach ($maps as $map) {
            $currentNumber = convert($currentNumber, $map);
        }
        $lowestLocation = $lowestLocation === null ? $currentNumber : min($lowestLocation, $currentNumber);
    }

    return $lowestLocation;
}

/**
 * Processes the input file to find the lowest location number for the seeds.
 *
 * @param string $filePath Path to the input file.
 * @return int|null The lowest location number, or null if the file can't be processed.
 */
function processSeeds(string $filePath): ?int {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $index = 0;

    $seeds = explode(' ', substr(trim($lines[$index++]), 7));
    $maps = [];
    while ($index < count($lines)) {
        if (trim($lines[$index]) === "") {
            $index++;
            continue;
        }
        if (str_contains($lines[$index], 'map:')) {
            $maps[] = [];
        } else {
            $maps[count($maps) - 1][] = parseMapping($lines[$index]);
        }

        $index++;
    }

    return getLowestLocation($seeds, $maps);
}

$filePath = 'input.txt';
echo "Lowest location number: " . processSeeds($filePath) . "\n";