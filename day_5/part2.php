<?php

function parseMapping(string $line): array {
    return explode(' ', trim($line));
}

/**
 * Converts a number range from one category to another based on provided mappings.
 *
 * @param array $range The number range to be converted.
 * @param array $mappings The mappings that define how numbers are converted between categories.
 * @return array An array of resulting ranges after conversion.
 */
function convertRange(array $range, array $mappings): array {
    $resultRanges = [];
    list($min, $max) = $range;

    foreach ($mappings as $mapping) {
        list($destStart, $sourceStart, $length) = $mapping;
        $sourceEnd = $sourceStart + $length;

        if ($min < $sourceEnd && $max >= $sourceStart) {
            $mappedMin = max($min, $sourceStart);
            $mappedMax = min($max, $sourceEnd - 1);

            $resultRanges[] = [$destStart + ($mappedMin - $sourceStart), $destStart + ($mappedMax - $sourceStart)];
        }
    }

    // If the range does not intersect with any mapping, add it as it is.
    if (empty($resultRanges)) {
        $resultRanges[] = $range;
    }

    return $resultRanges;
}

/**
 * Parses the seed ranges from the input lines.
 *
 * @param array $lines The lines of the input file.
 * @param int &$index Reference to the current line index.
 * @return array An array of seed ranges.
 */
function parseSeedRanges(array $lines, int &$index): array {
    $seedRangesLine = explode(' ', substr(trim($lines[$index++]), 7));
    $ranges = [];
    for ($i = 0; $i < count($seedRangesLine); $i += 2) {
        $start = (int)$seedRangesLine[$i];
        $length = (int)$seedRangesLine[$i + 1];
        $ranges[] = [$start, $start + $length - 1];
    }
    return $ranges;
}

/**
 * Parses the mappings from the input lines.
 *
 * @param array $lines The lines of the input file.
 * @param int &$index Reference to the current line index.
 * @return array An array of mappings.
 */
function parseMappings(array $lines, int &$index): array {
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
    return $maps;
}

/**
 * Processes the seed ranges through all mappings to find the lowest location number.
 *
 * @param array $ranges Seed ranges.
 * @param array $maps Mappings for conversion.
 * @return int|null The lowest location number, or null if no valid location found.
 */
function processRangesThroughMappings(array $ranges, array $maps): ?int {
    foreach ($maps as $map) {
        $newRanges = [];
        foreach ($ranges as $range) {
            $convertedRanges = convertRange($range, $map);
            $newRanges = array_merge($newRanges, $convertedRanges);
        }
        $ranges = $newRanges;
    }

    $lowestLocation = PHP_INT_MAX;
    foreach ($ranges as $range) {
        $lowestLocation = min($lowestLocation, $range[0], $range[1]);
    }

    return $lowestLocation === PHP_INT_MAX ? null : $lowestLocation;
}

/**
 * Main function to process seeds from the input file.
 *
 * @param string $filePath Path to the input file.
 * @return int|null The lowest location number, or null if the file can't be processed.
 */
function processSeeds(string $filePath): ?int {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $index = 0;

    $ranges = parseSeedRanges($lines, $index);
    $maps = parseMappings($lines, $index);

    return processRangesThroughMappings($ranges, $maps);
}

$filePath = 'input.txt';
echo "Lowest location number: " . processSeeds($filePath) . "\n";