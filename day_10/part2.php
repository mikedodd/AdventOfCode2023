<?php

const LINE_HORIZONTAL = '-';
const LINE_VERTICAL = '|';
const PIPE_FORWARD = 'F';
const PIPE_LEFT = 'L';
const PIPE_SEVEN = '7';
const PIPE_J = 'J';
const EXPANSIONS = [
    '.' => [
        ['M', 'M', 'M'],
        ['M', '.', 'M'],
        ['M', 'M', 'M'],
    ],
    '|' => [
        ['M', '|', 'M'],
        ['M', '|', 'M'],
        ['M', '|', 'M'],
    ],
    '-' => [
        ['M', 'M', 'M'],
        ['-', '-', '-'],
        ['M', 'M', 'M'],
    ],
    'L' => [
        ['M', '|', 'M'],
        ['M', 'L', '-'],
        ['M', 'M', 'M'],
    ],
    'J' => [
        ['M', '|', 'M'],
        ['-', 'J', 'M'],
        ['M', 'M', 'M'],
    ],
    '7' => [
        ['M', 'M', 'M'],
        ['-', '7', 'M'],
        ['M', '|', 'M'],
    ],
    'F' => [
        ['M', 'M', 'M'],
        ['M', 'F', '-'],
        ['M', '|', 'M'],
    ],
];

/**
 * Retrieves the coordinates adjacent to the given coordinates on the map.
 *
 * @param array $map The map array.
 * @param int $xCoordinate The X-coordinate.
 * @param int $yCoordinate The Y-coordinate.
 * @return array The adjacent coordinates.
 */
function getAdjacentCoordinates(array $map, int $xCoordinate, int $yCoordinate): array
{
    $adjacentCoordinates = [];
    if (in_array($map[$yCoordinate][$xCoordinate], ['S', '-', 'J', '7']) && isset($map[$yCoordinate][$xCoordinate - 1]) && in_array($map[$yCoordinate][$xCoordinate - 1], ['-', 'L', 'F'])) {
        $adjacentCoordinates[] = [$xCoordinate - 1, $yCoordinate];
    }
    if (in_array($map[$yCoordinate][$xCoordinate], ['S', '-', 'L', 'F']) && isset($map[$yCoordinate][$xCoordinate + 1]) && in_array($map[$yCoordinate][$xCoordinate + 1], ['-', 'J', '7'])) {
        $adjacentCoordinates[] = [$xCoordinate + 1, $yCoordinate];
    }
    if (in_array($map[$yCoordinate][$xCoordinate], ['S', '|', 'L', 'J']) && isset($map[$yCoordinate - 1][$xCoordinate]) && in_array($map[$yCoordinate - 1][$xCoordinate], ['|', '7', 'F'])) {
        $adjacentCoordinates[] = [$xCoordinate, $yCoordinate - 1];
    }
    if (in_array($map[$yCoordinate][$xCoordinate], ['S', '|', '7', 'F']) && isset($map[$yCoordinate + 1][$xCoordinate]) && in_array($map[$yCoordinate + 1][$xCoordinate], ['|', 'L', 'J'])) {
        $adjacentCoordinates[] = [$xCoordinate, $yCoordinate + 1];
    }
    return $adjacentCoordinates;
}

/**
 * Fills the adjacent coordinates with 'O' for the given coordinates on the grid.
 *
 * @param array &$grid The grid array.
 * @param int $x The X-coordinate.
 * @param int $y The Y-coordinate.
 *
 * @return void
 */
function fillAdjacentCoordinates(array &$grid, $x, $y): void
{
    if (!in_array($grid[$y][$x], ['M', '.'])) {
        return;
    }
    $grid[$y][$x] = 'O';
    // set O for all neighbours.
    $directions = [[-1, 0], [0, -1], [1, 0], [0, 1]];
    foreach ($directions as $direction) {
        fillAdjacentCoordinate($grid, $x + $direction[0], $y + $direction[1]);
    }
}

/**
 * Fills the adjacent coordinate based on the given X and Y coordinates.
 *
 * @param array $grid The grid array.
 * @param int $nx The X-coordinate.
 * @param int $ny The Y-coordinate.
 * @return void
 */
function fillAdjacentCoordinate(array &$grid, $nx, $ny): void
{
    if (isset($grid[$ny][$nx]) && in_array($grid[$ny][$nx], ['M', '.'])) {
        fillAdjacentCoordinates($grid, $nx, $ny);
    }
}

/**
 * Determines the type of starting pipe based on the positions of the neighbors.
 *
 * @param array $startingPosition The starting position coordinates.
 * @param array $neighbourPositions The positions of the two neighboring pipes.
 * @return string The type of starting pipe.
 */
function getStartingPipe(array $startingPosition, array $neighbourPositions): string
{
    [$startX, $startY] = $startingPosition;
    [$neighbour1, $neighbour2] = $neighbourPositions;
    [$neighbour1x, $neighbour1y] = $neighbour1;
    [$neighbour2x, $neighbour2y] = $neighbour2;

    if ($neighbour1y === $neighbour2y) {
        return LINE_HORIZONTAL;
    }

    if ($neighbour1x === $neighbour2x) {
        return LINE_VERTICAL;
    }

    if ($neighbour1x > $startX || $neighbour2x > $startX) {
        return ($neighbour1y > $startY || $neighbour2y > $startY) ? PIPE_FORWARD : PIPE_LEFT;
    }

    if ($neighbour1x < $startX || $neighbour2x < $startX) {
        return ($neighbour1y > $startY || $neighbour2y > $startY) ? PIPE_SEVEN : PIPE_J;
    }
}

/**
 * Expands the input data based on the given key.
 *
 * @param string $givenKey The given key to expand the data with.
 * @param array $inputData The input data to be expanded.
 * @return array The expanded data.
 */
function expandDataBasedOnGivenKey(string $givenKey, array $inputData): array
{
    $expansions = EXPANSIONS;
    $expansions['S'] = $expansions[$givenKey];
    $expandedData = [];
    foreach ($inputData as $yIndex => $row) {
        foreach ($row as $xIndex => $value) {
            $expandedCells = $expansions[$value];
            $expandedCellsCount = count($expandedCells);
            for ($indexA = 0; $indexA < $expandedCellsCount; $indexA++) {
                for ($indexB = 0; $indexB < $expandedCellsCount; $indexB++) {
                    $expandedData[3 * $yIndex + $indexA][3 * $xIndex + $indexB] = $expandedCells[$indexA][$indexB];
                }
            }
        }
    }
    return $expandedData;
}

/**
 * Computes the map traversal and returns the number of occurrences.
 *
 * @param array $data The map data.
 * @return int The number of occurrences.
 */
function computeMapTraversal(array $data): int
{
    foreach ($data as $key => $row) {
        $data[$key] = str_split($row);
        $position = array_search('S', $data[$key]);
        if (is_int($position)) {
            $startingPosition = [$position, $key];
        }
    }

    $inputData = $data;

    $positions = getAdjacentCoordinates($data, $startingPosition[0], $startingPosition[1]);
    $startingPipe = getStartingPipe($startingPosition, $positions);

    while (true) {
        foreach ($positions as $key => $position) {
            $newPosition = getAdjacentCoordinates($data, $position[0], $position[1]);
            $data[$position[1]][$position[0]] = 'S';
            if (empty($newPosition)) {
                break;
            }
            $positions[$key] = $newPosition[0];
        }
        if ($positions[0] == $positions[1]) {
            $data[$positions[0][1]][$positions[0][0]] = 'S';
            break;
        }
    }

    foreach ($inputData as $y => $row) {
        foreach ($row as $x => $value) {
            $inputData[$y][$x] = $data[$y][$x] === 'S' ? $value : '.';
        }
    }

    $expandedInputData = expandDataBasedOnGivenKey($startingPipe, $inputData);
    $inputData = fillCoordinates($expandedInputData);

    return countOccurrences($inputData);
}

/**
 * Fills the coordinates adjacent to the border non-solution tiles.
 *
 * @param array $inputData The input data array.
 * @return array The modified input data array with filled coordinates.
 */
function fillCoordinates(array $inputData): array
{
    for ($y = 0; $y < count($inputData); $y++) {
        for ($x = 0; $x < count($inputData[0]); $x++) {
            // We only initialise filling from border non solution tiles.
            if (!in_array($inputData[$y][$x], ['.', 'M'])) {
                continue;
            }
            if ($x === 0 || $x === count($inputData[0]) - 1 || $y === 0 || $y === count($inputData) - 1) {
                fillAdjacentCoordinates($inputData, $x, $y);
            }
        }
    }
    return $inputData;
}

/**
 * Counts the number of occurrences of the "." character in an array of input data.
 *
 * @param array $inputData The array of input data.
 * @return int The number of occurrences of the "." character.
 */
function countOccurrences(array $inputData): int
{
    $occurrenceCount = 0;
    foreach ($inputData as $line) {
        $values = array_count_values($line);
        $occurrenceCount += $values['.'] ?? 0;
    }
    return $occurrenceCount;
}

$mapInputData = file_get_contents('input.txt');
$data = explode(PHP_EOL, $mapInputData);
$startingPosition = null;
$steps = computeMapTraversal($data);

echo "Number of moves: " . $steps;
