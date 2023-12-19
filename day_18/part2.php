<?php

const FILEPATH = 'input.txt';

/**
 * Parses an input file and returns an array of movements.
 *
 * @param string $filePath The path to the input file.
 * @return array The array of movements, where each movement contains two sub-arrays:
 *               - The first sub-array contains the direction and distance in the format [$direction, $distance].
 *               - The second sub-array contains the secondary direction and distance in the format [$secondaryDirection, $secondaryDistance].
 * @throws Exception If unable to open the file.
 */
function parseInputFile(string $filePath): array
{
    $fileHandle = fopen($filePath, "r");

    $movements = [];
    while ($line = trim(fgets($fileHandle))) {
        [$direction, $distance, $hex] = explode(" ", $line);
        $secondaryDirection = ['R', 'D', 'L', 'U'][substr($hex, -2, 1)];
        $secondaryDistance = hexdec(substr($hex, 2, 5));
        $movements[] = [[$direction, $distance], [$secondaryDirection, $secondaryDistance]];
    }
    fclose($fileHandle);

    return $movements;
}

/**
 * Calculates the area and border length based on the given movements.
 *
 * @param array $movements The movements to calculate the area and border length from.
 *                        Each movement should be in the format [[$direction, $distance], ...].
 * @return array An associative array containing the calculated area and border length.
 *               The array has the following structure: ['area' => $area, 'borderLength' => $borderLength].
 */
function calculateAreaAndBorderLength(array $movements): array
{
    $borderLength = $shoelaceSum = 0;
    $currentPosition = [0, 0];

    foreach ($movements as $movement) {
        [$direction, $distance] = $movement[1];
        $borderLength += $distance;
        $nextPosition = getNextPosition($currentPosition, $direction, $distance);

        // Shoelace formula
        $shoelaceSum += $currentPosition[0] * $nextPosition[1] - $nextPosition[0] * $currentPosition[1];
        $currentPosition = $nextPosition;
    }

    $area = abs($shoelaceSum) / 2;
    return ['area' => $area, 'borderLength' => $borderLength];
}

/**
 * Calculates the next position based on the current position, direction, and distance.
 *
 * @param array $position The current position represented as an array [x, y].
 * @param string $direction The direction to move (R = right, D = down, L = left, U = up).
 * @param int $distance The distance to move.
 * @return array The next position represented as an array [x, y].
 */
function getNextPosition(array $position, string $direction, int $distance): array
{
    return match ($direction) {
        'R' => [$position[0] + $distance, $position[1]],
        'D' => [$position[0], $position[1] + $distance],
        'L' => [$position[0] - $distance, $position[1]],
        'U' => [$position[0], $position[1] - $distance],
    };
}

/**
 * Calculates the volume using the given area and border length based on Pick's theorem.
 *
 * @param int $area The area of the shape.
 * @param int $borderLength The length of the shape's border.
 * @return float|int The calculated volume.
 */
function calculateVolume(int $area, int $borderLength): float|int
{
    return $area + ($borderLength / 2) + 1;
}


$movements = parseInputFile(FILEPATH);

$part1Results = calculateAreaAndBorderLength($movements, 1);
$part1Volume = calculateVolume($part1Results['area'], $part1Results['borderLength']);

echo $part1Volume . " cubic meters\n";
