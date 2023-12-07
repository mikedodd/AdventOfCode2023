<?php

/**
 * Parses the race data from a file where race times and distances are concatenated.
 *
 * @param string $filePath Path to the input file.
 * @return array Associative array containing 'time' and 'distance' for the race.
 */
function parseConcatenatedRaceData(string $filePath): array {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);

    // Extract numbers from each line and convert them to integers
    $time = preg_replace('/[^0-9]/', '', $lines[0]);
    $distance = preg_replace('/[^0-9]/', '', $lines[1]);

    return ['time' => (int)$time, 'distance' => (int)$distance];
}

/**
 * Calculates the number of ways to win a single long race by holding the button
 * for different durations at the start.
 *
 * @param int $time Total time available for the race.
 * @param int $record The record distance that needs to be beaten.
 * @return int The number of ways to beat the record.
 */
function calculateWinningStrategiesForLongRace(int $time, int $record): int {
    $waysToWin = 0;

    // Iterate over all possible hold times and calculate the distance traveled
    for ($holdTime = 1; $holdTime <= $time - 1; $holdTime++) {
        $speed = $holdTime;
        $travelTime = $time - $holdTime;
        $distanceTraveled = $speed * $travelTime;

        // Increment the count if the distance traveled is greater than the record
        if ($distanceTraveled > $record) {
            $waysToWin++;
        }
    }

    return $waysToWin;
}

/**
 * Processes the input file to calculate the total number of ways to win
 * the race given the concatenated race times and distances.
 *
 * @param string $filePath Path to the input file.
 * @return int The total number of ways to win the race.
 */
function calculateTotalWinningWaysForLongRace(string $filePath): int {
    $raceData = parseConcatenatedRaceData($filePath);
    return calculateWinningStrategiesForLongRace($raceData['time'], $raceData['distance']);
}

// Path to the input file
$filePath = 'input.txt';
echo "Total winning ways for the long race: " . calculateTotalWinningWaysForLongRace($filePath) . "\n";