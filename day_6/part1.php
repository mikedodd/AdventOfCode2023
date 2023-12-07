<?php

/**
 * Parses the race data from a file, extracting times and distances for each race.
 *
 * @param string $filePath Path to the input file.
 * @return array An array of associative arrays containing 'time' and 'distance' for each race.
 */
function parseRaceData(string $filePath): array {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);

    // Extract and clean up the time and distance data from the file
    $times = array_values(array_filter(explode(' ', preg_replace('/[^0-9\s]/', '', $lines[0]))));
    $distances = array_values(array_filter(explode(' ', preg_replace('/[^0-9\s]/', '', $lines[1]))));

    $raceData = [];
    $numRaces = min(count($times), count($distances));

    // Pair corresponding time and distance values for each race
    for ($i = 0; $i < $numRaces; $i++) {
        $raceData[] = ['time' => (int)$times[$i], 'distance' => (int)$distances[$i]];
    }

    return $raceData;
}

/**
 * Calculates the number of ways to win a race by holding the button for different durations.
 *
 * @param int $time Total time available for the race.
 * @param int $record The record distance that needs to be beaten.
 * @return int The number of ways to beat the record.
 */
function calculateWinningStrategies(int $time, int $record): int {
    $waysToWin = 0;

    // Iterate over possible button hold times and calculate the resultant distance
    for ($holdTime = 1; $holdTime < $time; $holdTime++) {
        $speed = $holdTime;
        $travelTime = $time - $holdTime;
        $distanceTraveled = $speed * $travelTime;

        // Count the number of ways to exceed the record distance
        if ($distanceTraveled > $record) {
            $waysToWin++;
        }
    }

    return $waysToWin;
}

/**
 * Calculates the total number of ways to win across all races.
 *
 * @param string $filePath Path to the input file.
 * @return int The product of winning ways across all races.
 */
function calculateTotalWinningWays(string $filePath): int {
    $raceData = parseRaceData($filePath);
    $totalWays = 1;

    // Multiply the number of winning strategies for each race
    foreach ($raceData as $race) {
        $totalWays *= calculateWinningStrategies($race['time'], $race['distance']);
    }

    return $totalWays;
}

$filePath = 'input.txt';
echo "Total winning ways: " . calculateTotalWinningWays($filePath) . "\n";