<?php

const GALAXY_SYMBOL = '#';
const FILEPATH = 'input.txt';

/**
 * Reads the content of a file and returns it as an array of strings.
 *
 * @param string $filePath The path to the file to be read.
 *
 * @return array An array of strings, where each element represents a line from the file.
 */
function readInput(string $filePath): array {
    return array_map('str_split', file($filePath, FILE_IGNORE_NEW_LINES));
}

/**
 * Finds the positions of the galaxies in the given data.
 *
 * @param array $data The data containing the galaxy positions.
 *
 * @return array The positions of the galaxies.
 */
function findGalaxyPositions(array $data): array
{
    $positions = [];
    foreach ($data as $y => $row) {
        foreach ($row as $x => $value) {
            if (isGalaxyPoint($value)) {
                $positions[] = [$x, $y];
            }
        }
    }
    return $positions;
}

/**
 *  Check if the value is galaxy point.
 *
 *  @param mixed $value
 *
 *  @return bool
 */
function isGalaxyPoint($value): bool
{
    return $value === GALAXY_SYMBOL;
}

/**
 * Expand the given positions array by finding the missing x and y positions within the specified row and column count.
 *
 * @param array $positions An array of positions [x, y]
 * @param int $rowCount The total number of rows
 * @param int $colCount The total number of columns
 *
 * @return array An array containing expanded x and y positions.
 */
function expandPositions(array $positions, int $rowCount, int $colCount): array
{
    $xPositions = array_column($positions, 0);
    $yPositions = array_column($positions, 1);
    $xToExpand = array_diff(range(0, $colCount - 1), array_unique($xPositions));
    $yToExpand = array_diff(range(0, $rowCount - 1), array_unique($yPositions));
    return [$xToExpand, $yToExpand];
}

/**
 * Calculates the expanded positions based on the current position and given arrays.
 *
 * @param array $currentPos The current position [$x, $y].
 * @param array $positions An array of positions [$pos1, $pos2, ...].
 * @param array $xToExpand An array of x values to expand.
 * @*/
function calculateExpandedPositions(array $currentPos, array $positions, array $xToExpand, array $yToExpand): array
{
    $distX = 0;
    $distY = 0;
    foreach ($positions as $pos) {
        $distX += abs($currentPos[0] - $pos[0]);
        $distY += abs($currentPos[1] - $pos[1]);
        foreach ($xToExpand as $x) {
            if ($currentPos[0] < $x && $x < $pos[0] || $pos[0] < $x && $x < $currentPos[0]) {
                $distX++;
            }
        }
        foreach ($yToExpand as $y) {
            if ($currentPos[1] < $y && $y < $pos[1] || $pos[1] < $y && $y < $currentPos[1]) {
                $distY++;
            }
        }
    }
    return [$distX, $distY];
}

/**
 * Calculate the total distance given an array of positions and data.
 *
 * @param array $positions An array of positions.
 * @param array $data An array of data.
 *
 * @return int The calculated total distance.
 */
function calculateTotalDistance(array $positions, array $data): int
{
    $rowCount = count($data);
    $colCount = count($data[0]);
    list($xToExpand, $yToExpand) = expandPositions($positions, $rowCount, $colCount);
    $totalDistance = 0;
    while ($positions) {
        $currentPos = array_pop($positions);
        list($distX, $distY) = calculateExpandedPositions($currentPos, $positions, $xToExpand, $yToExpand);
        $totalDistance += $distX + $distY;
    }
    return $totalDistance;
}

$inputData = readInput(FILEPATH);
$galaxyPositions = findGalaxyPositions($inputData);
$totalDistance = calculateTotalDistance($galaxyPositions, $inputData);

echo "Total distance: " . $totalDistance;
