<?php

const GALAXY_SYMBOL = '#';
const FILEPATH = 'input.txt';
const EXPANSION_FACTOR = 1000000;

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
 * Determines if the given value is a galaxy point.
 *
 * @param mixed $value The value to check.
 *
 * @return bool Returns true if the value is a galaxy point, otherwise false.
 */
function isGalaxyPoint($value): bool
{
    return $value === GALAXY_SYMBOL;
}

/**
 * Calculates the total distance between all positions.
 *
 * @param array $positions An array of positions, where each position is represented as an array with two elements [x, y].
 * @param array $data The data array.
 * @param int $expansionFactor The expansion factor to account for expanded positions.
 *
 * @return int The total distance.
 */
function calculateTotalDistance(array $positions, array $data, int $expansionFactor): int
{
    list($rowCount, $colCount, $xToExpand, $yToExpand) = prepareData($positions, $data);
    return calculateDistance($positions, $xToExpand, $yToExpand, $expansionFactor);
}

/**
 * Prepares the data by calculating various values and returning an array of results.
 *
 * @param array $positions An array containing the positions of data points.
 * @param array $data The data points in a two-dimensional array.
 *
 * @return array Returns an array with the following values:
 *  - $rowCount: The number of rows in the data array.
 *  - $colCount: The number of columns in the data array.
 *  - $xToExpand: An array of column indices that need to be expanded (not present in $positions).
 *  - $yToExpand: An array of row indices that need to be expanded (not present in $positions).
 */
function prepareData(array $positions, array $data): array
{
    $rowCount = count($data);
    $colCount = count($data[0]);
    $xPositions = array_column($positions, 0);
    $yPositions = array_column($positions, 1);
    $xToExpand = array_diff(range(0, $colCount - 1), array_unique($xPositions));
    $yToExpand = array_diff(range(0, $rowCount - 1), array_unique($yPositions));
    return [$rowCount, $colCount, $xToExpand, $yToExpand];
}

/**
 * Calculates the total distance between positions.
 *
 * @param array $positions An array of positions.
 * @param array $xToExpand An array of x-coordinates to expand.
 * @param array $yToExpand An array of y-coordinates to expand.
 * @param int $expansionFactor The expansion factor.
 *
 * @return int The total distance between the positions.
 */
function calculateDistance(array $positions, array $xToExpand, array $yToExpand, int $expansionFactor): int
{
    $totalDistance = 0;
    while ($positions) {
        $currentPos = array_pop($positions);
        foreach ($positions as $pos) {
            list($distX, $distY) = calculateDistances($currentPos, $pos, $xToExpand, $yToExpand, $expansionFactor);
            $totalDistance += $distX + $distY;
        }
    }
    return $totalDistance;
}

/**
 * Calculates the distances between the current position and a given position.
 *
 * @param array $currentPos The current position [x, y].
 * @param array $pos The position to calculate the distances to [x, y].
 * @param array $xToExpand An array of X-values to expand.
 * @param array $yToExpand An array of Y-values to expand.
 * @param int $expansionFactor The expansion factor.
 *
 * @return array Returns an array with the distances [distX, distY].
 */
function calculateDistances(array $currentPos, array $pos, array $xToExpand, array $yToExpand, int $expansionFactor): array
{
    $distX = abs($currentPos[0] - $pos[0]);
    $distY = abs($currentPos[1] - $pos[1]);

    foreach ($xToExpand as $x) {
        if (shouldExpand($currentPos[0], $pos[0], $x)) {
            $distX += $expansionFactor - 1;
        }
    }

    foreach ($yToExpand as $y) {
        if (shouldExpand($currentPos[1], $pos[1], $y)) {
            $distY += $expansionFactor - 1;
        }
    }
    return [$distX, $distY];
}

/**
 * Determines if the given value should be expanded based on the current position and the target position.
 *
 * @param int $currentPos The current position.
 * @param int $pos The target position.
 * @param int $value The value to check.
 *
 * @return bool Returns true if the value should be expanded, otherwise false.
 */
function shouldExpand(int $currentPos, int $pos, int $value): bool
{
    return ($currentPos < $value && $value < $pos) || ($pos < $value && $value < $currentPos);
}

$inputData = readInput(FILEPATH);
$galaxyPositions = findGalaxyPositions($inputData);
$totalDistance = calculateTotalDistance($galaxyPositions, $inputData, EXPANSION_FACTOR);

echo "Total distance with expansion: " . $totalDistance;
