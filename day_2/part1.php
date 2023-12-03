<?php

/**
 * Parses a game string to extract game ID and cube details.
 *
 * @param string $gameString The game string to parse.
 * @return array An associative array with game ID and cube details.
 */
function parseGameData($gameString): array
{
    preg_match('/Game (\d+): (.+)/', $gameString, $matches);
    $gameId = $matches[1];
    $rounds = explode(';', $matches[2]);
    $cubes = [];

    foreach ($rounds as $round) {
        preg_match_all('/(\d+) (red|green|blue)/', $round, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cubes[] = ['count' => $match[1], 'color' => $match[2]];
        }
    }

    return ['id' => $gameId, 'cubes' => $cubes];
}

/**
 * Checks if a game is possible given the limits of cubes.
 *
 * @param array $gameData The game data to check.
 * @param array $limits The limits for each color of cube.
 * @return bool True if the game is possible, false otherwise.
 */
function isGamePossible($gameData, $limits): bool
{
    foreach ($gameData['cubes'] as $cube) {
        if ($cube['count'] > $limits[$cube['color']]) {
            return false;
        }
    }
    return true;
}

/**
 * Reads game data from a file and calculates the sum of IDs for possible games.
 *
 * @param string $filePath Path to the file containing game data.
 * @param array $limits The limits for each color of cube.
 * @return int Sum of the IDs of possible games.
 */
function getGameIds(string $filePath, array $limits): int
{
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Main logic
    $possibleGameSum = 0;
    foreach ($lines as $gameString) {
        $gameData = parseGameData($gameString);
        if (isGamePossible($gameData, $limits)) {
            $possibleGameSum += $gameData['id'];
        }
    }

    return $possibleGameSum;
}

$filePath = 'input.txt';
$limits = ['red' => 12, 'green' => 13, 'blue' => 14];

$possibleGameSum = getGameIds($filePath, $limits);

echo "Sum of the IDs of possible games: $possibleGameSum\n";
