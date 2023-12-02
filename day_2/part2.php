<?php

/**
 * Parses game data from a string.
 *
 * @param string $gameString The string containing game data.
 * @return array An array containing the game ID and cubes data.
 */
function parseGameData(string $gameString): array
{
    preg_match('/Game (\d+): (.+)/', $gameString, $matches);
    $gameId = (int) $matches[1];
    $rounds = explode(';', $matches[2]);
    $cubes = [];

    foreach ($rounds as $round) {
        preg_match_all('/(\d+) (red|green|blue)/', $round, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $cubes[] = ['count' => (int) $match[1], 'color' => $match[2]];
        }
    }

    return ['id' => $gameId, 'cubes' => $cubes];
}

/**
 * Determines the minimum set of cubes required for a game.
 *
 * @param array $gameData The parsed game data.
 * @return array An array of the minimum number of each color cube required.
 */
function getMinimumCubeSet(array $gameData): array
{
    $minimumSet = [];

    foreach ($gameData['cubes'] as $cube) {
        $color = $cube['color'];
        $count = $cube['count'];
        $minimumSet[$color] = max($minimumSet[$color] ?? 0, $count);
    }

    return $minimumSet;
}

/**
 * Calculates the product of cube counts.
 *
 * @param array $cubeSet An array of cube counts.
 * @return int The product of the cube counts.
 */
function calculateCubeProduct(array $cubeSet): int
{
    return array_product($cubeSet);
}

/**
 * Processes game data from a file and calculates the sum of cube products.
 *
 * @param string $filePath Path to the file containing game data.
 * @return int Sum of cube products for each game.
 */
function processGameFile(string $filePath): int
{
    $gameStrings = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $totalPower = 0;

    foreach ($gameStrings as $gameString) {
        $gameData = parseGameData($gameString);
        $minimumSet = getMinimumCubeSet($gameData);
        $totalPower += calculateCubeProduct($minimumSet);
    }

    return $totalPower;
}

$filePath = 'input.txt';

$totalCubePower = processGameFile($filePath);
echo "Sum of cube power: $totalCubePower\n";