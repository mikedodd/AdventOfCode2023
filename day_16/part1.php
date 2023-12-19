<?php

const FILEPATH = 'input.txt';

/**
 * Handles direction splitters based on the current position and direction.
 *
 * @param array $grid The grid map.
 * @param array $activeMap The active map.
 * @param array $directionMap The direction map.
 * @param int $currentX The current x-coordinate.
 * @param int $currentY The current y-coordinate.
 * @param string $direction The current direction.
 * @return bool Returns true if direction splitters are handled successfully, false otherwise.
 */
function handleDirectionSplitters($grid, &$activeMap, &$directionMap, $currentX, $currentY, $direction): bool
{
    if ($grid[$currentY][$currentX] === '|' && in_array($direction, ['L', 'R'])) {
        traverseGrid($grid, $activeMap, $directionMap, $currentX, $currentY - 1, 'U');
        traverseGrid($grid, $activeMap, $directionMap, $currentX, $currentY + 1, 'D');
        return true;
    }
    if ($grid[$currentY][$currentX] === '-' && in_array($direction, ['U', 'D'])) {
        traverseGrid($grid, $activeMap, $directionMap, $currentX + 1, $currentY, 'R');
        traverseGrid($grid, $activeMap, $directionMap, $currentX - 1, $currentY, 'L');
        return true;
    }
    return false;
}

// Handling mirrors
function handleMirrorReflections($grid, $currentPositionX, $currentPositionY, &$currentDirection): bool
{
    if ($grid[$currentPositionY][$currentPositionX] == '\\') {
        $currentDirection = match ($currentDirection) {
            'R' => 'D',
            'L' => 'U',
            'U' => 'L',
            'D' => 'R',
        };
        return true;
    }
    if ($grid[$currentPositionY][$currentPositionX] == '/') {
        $currentDirection = match ($currentDirection) {
            'R' => 'U',
            'L' => 'D',
            'U' => 'R',
            'D' => 'L',
        };
        return true;
    }
    return false;
}

/**
 * Traverses the given grid starting from the current cell and moving in the specified direction.
 *
 * @param array $grid The grid to traverse.
 * @param array &$markedCells The array to keep track of visited cells. Passed by reference.
 * @param array &$directionMap The map to store directions for each cell. Passed by reference. Default value is an empty array.
 * @param int $currentX The current X position in the grid. Default value is 0.
 * @param int $currentY The current Y position in the grid. Default value is 0.
 * @param string $currentDirection The current direction. Default value is 'R'.
 * @return void
 */
function traverseGrid(array $grid, array &$markedCells, array &$directionMap = [], int $currentX = 0, int $currentY = 0, string $currentDirection = 'R'): void
{
    // Check if current cell exists in the grid
    if (!isset($grid[$currentY][$currentX])) {
        return;
    }

    // Mark the current cell as visited
    $markedCells[$currentY][$currentX] = 1;

    // If current direction is already in the direction map for current cell, return
    if (isset($directionMap[$currentY][$currentX]) && in_array($currentDirection, $directionMap[$currentY][$currentX])) {
        return;
    }

    // Add current direction to the direction map for current cell
    $directionMap[$currentY][$currentX][] = $currentDirection;

    // Handle direction splitters
    if (handleDirectionSplitters($grid, $markedCells, $directionMap, $currentX, $currentY, $currentDirection)) {
        return;
    }

    // Handle mirror reflections
    handleMirrorReflections($grid, $currentX, $currentY, $currentDirection);

    // Determine direction vector based on current direction
    [$dX, $dY] = match ($currentDirection) {
        'R' => [1, 0],
        'L' => [-1, 0],
        'U' => [0, -1],
        'D' => [0, 1],
    };

    // Continue traversing the grid in the current direction
    traverseGrid($grid, $markedCells, $directionMap, $currentX + $dX, $currentY + $dY, $currentDirection);
}

/**
 * Counts the number of energized tiles in the given grid map.
 *
 * @return int The total count of energized tiles in the grid map.
 */
function getEnergizedCount(){
    // Read input from file
    $input = file_get_contents(FILEPATH);

    $input = explode(PHP_EOL, trim($input));
    foreach ($input as $key => $row) {
        $input[$key] = str_split($row);
    }

    $energizedMap = $input;
    traverseGrid($input, $energizedMap);

    $energizedCount = 0;
    foreach ($energizedMap as $row) {
        $energizedCount += array_count_values($row)[1] ?? 0;
    }

    return $energizedCount;
}

echo "Energized tiles: " . getEnergizedCount() . PHP_EOL;