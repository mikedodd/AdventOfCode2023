<?php

ini_set("memory_limit", "-1");

const FILENAME = 'input.txt';

const DIRECTIONS = [[-1, 0], [0, 1], [1, 0], [0, -1]];

const MIN_STREAK = 4;
const MAX_STREAK = 10;

/**
 * Calculate the heat loss to reach the destination on the given map.
 *
 * @return int|string The heat loss to reach the destination, or a string indicating that the destination was not reached.
 */
function calculateHeatLoss(): int|string
{
    $map = parseMap(FILENAME);
    $rows = count($map);
    $columns = count($map[0]);
    $destination = ['row' => $rows - 1, 'col' => $columns - 1];

    $pq = new SplPriorityQueue();
    $seen = [];

    // Start facing North and West, then turn and move
    enqueueState($pq, ['row' => 0, 'col' => 0], 3, 0, 0); // North
    enqueueState($pq, ['row' => 0, 'col' => 0], 0, 0, 0); // West

    while (!$pq->isEmpty()) {
        echo count($pq) . "\r\n";

        $state = $pq->extract();
        $stateKey = serializeState($state);
        if (isset($seen[$stateKey])) {
            continue;
        }

        $seen[$stateKey] = true;

        if ($state['position'] == $destination) {
            return $state['heat_loss'];
        }

        // Move left, right, and straight
        moveLeft($pq, $map, $state);
        moveRight($pq, $map, $state);
        moveStraight($pq, $map, $state);
    }

    return "The destination was not reached.";
}

/**
 * Move the player straight in the current direction if the number of moves
 * in the current direction is less than or equal to 9, and update the state
 * accordingly.
 *
 * @param SplPriorityQueue $pq The priority queue containing the states.
 * @param array $map The map.
 * @param array $state The current state of the player, with keys 'position',
 *                     'direction', 'moves_in_dir', and 'heat_loss'.
 * @return void
 */
function moveStraight(SplPriorityQueue $pq, array $map, array $state): void
{
    if ($state['moves_in_dir'] <= 9) {
        $newState = $state;
        $newState['position'] = addPoints($state['position'], DIRECTIONS[$state['direction']]);
        if (inLimits($map, $newState['position'])) {
            $newState['heat_loss'] += $map[$newState['position']['row']][$newState['position']['col']];
            $newState['moves_in_dir']++;
            enqueueState($pq, $newState['position'], $newState['direction'], $newState['moves_in_dir'], $newState['heat_loss']);
        }
    }
}

/**
 * Move the position to the right and enqueue the new state if it is within the map limits.
 *
 * @param SplPriorityQueue $pq The priority queue to enqueue the new state.
 * @param array $map The map.
 * @param array $state The current state, with the keys 'direction', 'moves_in_dir', 'position', and 'heat_loss'.
 */
function moveRight(SplPriorityQueue $pq, array $map, array $state): void
{
    $newState = $state;
    $newState['direction'] = ($state['direction'] + 1) % 4;
    $newState['moves_in_dir'] = 0;
    $movedFourSteps = true;
    for ($i = 0; $i < 4; $i++) {
        $newState['position'] = addPoints($newState['position'], DIRECTIONS[$newState['direction']]);
        $movedFourSteps = inLimits($map, $newState['position']);
        if (!$movedFourSteps) break;
        $newState['heat_loss'] += $map[$newState['position']['row']][$newState['position']['col']];
        $newState['moves_in_dir']++;
    }
    if ($movedFourSteps) {
        enqueueState($pq, $newState['position'], $newState['direction'], $newState['moves_in_dir'], $newState['heat_loss']);
    }
}


/**
 * Move the position to the left direction in the map and enqueue the new state if it remains within the map limits.
 *
 * @param SplPriorityQueue $pq The priority queue for storing the states.
 * @param array $map The map.
 * @param array $state The current state containing position, direction, moves in current direction, and heat loss.
 */
function moveLeft(SplPriorityQueue $pq, array $map, array $state)
{
    $newState = $state;
    $newState['direction'] = ($state['direction'] - 1 + 4) % 4;
    $newState['moves_in_dir'] = 0;
    $movedFourSteps = true;
    for ($i = 0; $i < 4; $i++) {
        $newState['position'] = addPoints($newState['position'], DIRECTIONS[$newState['direction']]);
        $movedFourSteps = inLimits($map, $newState['position']);
        if (!$movedFourSteps) break;
        $newState['heat_loss'] += $map[$newState['position']['row']][$newState['position']['col']];
        $newState['moves_in_dir']++;
    }
    if ($movedFourSteps) {
        enqueueState($pq, $newState['position'], $newState['direction'], $newState['moves_in_dir'], $newState['heat_loss']);
    }
}

/**
 * Check if the given position is within the limits of the map.
 *
 * @param array $map The map.
 * @param array $position The position to check, with the keys 'row' and 'col'.
 * @return bool Whether the position is within the map limits.
 */
function inLimits(array $map, array $position): bool
{
    return $position['row'] >= 0 && $position['row'] < count($map) && $position['col'] >= 0 && $position['col'] < count($map[0]);
}

/**
 * Adds two points together and returns the resulting point.
 *
 * @param array $p1 The first point, containing 'row' and 'col' keys.
 * @param array $p2 The second point, containing two values representing row and column offsets.
 *
 * @return array The resulting point, containing 'row' and 'col' keys, where 'row' is the sum of 'row' in $p1 and the
 *               first value in $p2, and 'col' is the sum of 'col' in $p1 and the second value in $p2.
 */
function addPoints(array $p1, array $p2): array
{
    return ['row' => $p1['row'] + $p2[0], 'col' => $p1['col'] + $p2[1]];
}


/**
 * Serialize the given state into a string.
 *
 * @param array $state The state to serialize, with the keys 'position', 'direction', and 'moves_in_dir'.
 * @return string The serialized state as a string.
 */
function serializeState(array $state): string
{
    return serialize([$state['position'], $state['direction'], $state['moves_in_dir']]);
}

/**
 * Enqueues a state into the priority queue.
 *
 * @param SplPriorityQueue $pq The priority queue to enqueue the state into.
 * @param array $position The position of the state.
 * @param int $direction The direction of the state.
 * @param int $moves_in_dir The number of moves in the direction of the state.
 * @param int $heat_loss The heat loss of the state.
 *
 * @return void
 */
function enqueueState(SplPriorityQueue $pq, array $position, int $direction, int $moves_in_dir, int $heat_loss): void
{
    $pq->insert(['position' => $position, 'direction' => $direction, 'moves_in_dir' => $moves_in_dir, 'heat_loss' => $heat_loss], -$heat_loss);
}

/**
 * Parses a map file and returns it as a 2-dimensional array of integers.
 *
 * @param string $filename The path to the map file.
 *
 * @return array Returns a 2-dimensional array where each element represents a row on the map, and each row consists
 *               of integers representing the individual cells on that row.
 *
 * @throws Exception Throws an exception if the file cannot be read or if it is not a valid map file.
 */
function parseMap(string $filename): array
{
    $file = file_get_contents($filename);
    return array_map(fn($line) => array_map('intval', str_split(trim($line))), explode("\n", $file));
}


/**
 * Enqueue a position if it is valid within the matrix.
 *
 * @param SplPriorityQueue $queue The priority queue to enqueue the position into.
 * @param array $matrix The matrix containing the values.
 * @param int $totalRows The total number of rows in the matrix.
 * @param int $totalColumns The total number of columns in the matrix.
 * @param int $currentRow The current row position.
 * @param int $currentColumn The current column position.
 * @param int $rowDirection The direction of movement in rows.
 * @param int $columnDirection The direction of movement in columns.
 * @param int $stepCount The current step count.
 * @param float $heatLoss The current heat loss value.
 * @return void
 */
function enqueueIfValid(
    SplPriorityQueue $queue,
    array $matrix,
    int $totalRows,
    int $totalColumns,
    int $currentRow,
    int $currentColumn,
    int $rowDirection,
    int $columnDirection,
    int $stepCount,
    float $heatLoss
): void {
    $nextRow = $currentRow + $rowDirection;
    $nextColumn = $currentColumn + $columnDirection;

    if ($nextRow >= 0 && $nextRow < $totalRows && $nextColumn >= 0 && $nextColumn < $totalColumns) {
        $updatedHeatLoss = $heatLoss + $matrix[$nextRow][$nextColumn];
        $queue->insert(['heatLoss' => $updatedHeatLoss, 'row' => $nextRow, 'column' => $nextColumn,
            'rowDirection' => $rowDirection, 'columnDirection' => $columnDirection,
            'stepCount' => $stepCount + 1],
            -$updatedHeatLoss);
    }
}

/**
 * Parses the contents of a file specified by the given path into a grid matrix.
 *
 * @param string $path The path of the file to parse.
 *
 * @return array An array containing the grid matrix, the number of rows in the matrix, and the number of columns in the matrix.
 */
function parsePathToGrid(string $path): array
{
    $file = file_get_contents($path);
    $matrix = array_map(fn($row) => array_map('intval', str_split(trim($row))), explode("\n", $file));
    return [$matrix, count($matrix), count($matrix[0])];
}

/**
 * Retrieves the allowed directions based on the current direction, number of steps, matrix size,
 * and current position.
 *
 * This function determines the directions in which the current position can move based on the given
 * direction, number of steps, and matrix size. It returns an array of the allowed directions.
 *
 * @param string $dir The current direction (e.g., 'north', 'west', etc.).
 * @param int $stepsInDir The number of steps in the current direction.
 * @param int $matrixSize The size of the matrix.
 * @param array $pos The current position in the matrix (e.g., ['row' => 0, 'col' => 0]).
 * @return array An array containing the allowed directions.
 */
function getAllowedDirections(string $dir, int $stepsInDir, int $matrixSize, array $pos): array
{
    $directions = ['north', 'west', 'south', 'east'];
    $allowedDirs = [];

    if ($stepsInDir === 0) {
        return $directions; // Initial state can move in any direction
    }

    // Add left and right of current direction
    $dirIndex = array_search($dir, $directions);
    $allowedDirs[] = $directions[($dirIndex + 1) % 4];
    $allowedDirs[] = $directions[($dirIndex + 3) % 4];

    // Check if it can continue in the same direction
    if ($stepsInDir < MAX_STREAK) {
        $allowedDirs[] = $dir;
    }

    // Filter out directions that go out of bounds
    return array_filter($allowedDirs, function ($d) use ($pos, $matrixSize) {
        $newPos = [
            'row' => $pos['row'] + DIRECTIONS[$d][0],
            'col' => $pos['col'] + DIRECTIONS[$d][1]
        ];
        return $newPos['row'] >= 0 && $newPos['row'] < $matrixSize && $newPos['col'] >= 0 && $newPos['col'] < $matrixSize;
    });
}

/**
 * Retrieves the initial states for a given matrix and source cell.
 *
 * @param array $matrix The matrix containing the values.
 * @param array $sourceCell The coordinates of the source cell in the matrix.
 *
 * @return array Returns an array containing the initial states for the matrix and source cell.
 */
function getInitialStates(array $matrix, array $sourceCell): array
{
    list($sourceRow, $sourceColumn) = $sourceCell;
    return [
        [$matrix[$sourceRow][$sourceColumn + 1], [$sourceRow, $sourceColumn + 1], [[0, 1], 1]],
        [$matrix[$sourceRow + 1][$sourceColumn], [$sourceRow + 1, $sourceColumn], [[1, 0], 1]]
    ];
}

/**
 * Initializes a priority queue with initial states.
 *
 * @param array $initialStates An array of initial states to add to the queue.
 *
 * @return SplPriorityQueue Returns a priority queue initialized with the given initial states.
 */
function initializeQueue(array $initialStates): SplPriorityQueue
{
    $queue = new SplPriorityQueue();
    foreach ($initialStates as $state) {
        $queue->insert($state, -$state[0]);
    }
    return $queue;
}


/**
 * Checks if a state has been visited.
 *
 * @param array $visitedStates The array of visited states.
 * @param int $row The row of the current state.
 * @param int $column The column of the current state.
 * @param int $rowDir The direction of movement in the row.
 * @param int $columnDir The direction of movement in the column.
 * @param int $numOfDirs The number of possible directions of movement.
 *
 * @return bool Returns true if the state has been visited, false otherwise.
 */
function isStateVisited(array &$visitedStates, int $row, int $column, int $rowDir, int $columnDir, int $numOfDirs): bool
{
    $currentState = serialize([[$row, $column], [[$rowDir, $columnDir], $numOfDirs]]);
    return in_array($currentState, $visitedStates);
}

/**
 * Processes the current state in the given matrix and enqueues valid adjacent states.
 *
 * @param SplPriorityQueue $queue The queue of states to process.
 * @param array $matrix The matrix representing the current state.
 * @param int $matrixSize The size of the matrix.
 * @param int $row The row of the current state.
 * @param int $column The column of the current state.
 * @param int $rowDir The row direction of the current state.
 * @param int $columnDir The column direction of the current state.
 * @param int $streak
 * @param float $heatLoss The heat loss for each state.
 * @return void
 */
function processCurrentState(SplPriorityQueue &$queue, array $matrix, int $matrixSize, int $row, int $column, int $rowDir, int $columnDir, int $streak, float $heatLoss): void
{
    // Continue in the same direction if the streak is less than the maximum
    if ($streak < 10) {
        $newRow = $row + $rowDir;
        $newColumn = $column + $columnDir;
        if (isWithinMatrixLimits($newRow, $newColumn, $matrixSize)) {
            enqueueIfValid($queue, $matrix, $matrixSize, $newRow, $newColumn, $rowDir, $columnDir, $streak + 1, $heatLoss);
        }
    }

    // Change direction if the streak is at least the minimum
    if ($streak >= 4) {
        foreach (DIRECTIONS as list($newRowDir, $newColumnDir)) {
            if (shouldChangeDirection($rowDir, $columnDir, $newRowDir, $newColumnDir)) {
                $newRow = $row + $newRowDir;
                $newColumn = $column + $newColumnDir;
                if (isWithinMatrixLimits($newRow, $newColumn, $matrixSize)) {
                    enqueueIfValid($queue, $matrix, $matrixSize, $newRow, $newColumn, $newRowDir, $newColumnDir, 1, $heatLoss);
                }
            }
        }
    }
}

/**
 * Checks if the current direction is different from the new direction.
 *
 * This function determines whether the current direction (specified by the row and column indices) is different from
 * the new direction. It returns true if the current direction is different from the new direction; otherwise, it returns false.
 *
 * @param mixed $currentRowDir The current row direction.
 * @param mixed $currentColumnDir The current column direction.
 * @param mixed $newRowDir The new row direction.
 * @param mixed $newColumnDir The new column direction.
 * @return bool Returns true if the current direction is different from the new direction; otherwise, false.
 */
function shouldChangeDirection($currentRowDir, $currentColumnDir, $newRowDir, $newColumnDir): bool
{
    return !($currentRowDir == $newRowDir && $currentColumnDir == $newColumnDir);
}

/**
 * Determines if a given direction should be enqueued based on the current direction and the new direction.
 *
 * @param int $rowDir The current direction on the row axis.
 * @param int $columnDir The current direction on the column axis.
 * @param int $newRowDir The new direction on the row axis.
 * @param int $newColumnDir The new direction on the column axis.
 *
 * @return bool Returns true if the new direction is different from the current direction and its opposite direction,
 *              false otherwise.
 */
function shouldEnqueueDirection(int $rowDir, int $columnDir, int $newRowDir, int $newColumnDir): bool
{
    return [$newRowDir, $newColumnDir] != [$rowDir, $columnDir] && [$newRowDir, $newColumnDir] != [-$rowDir, -$columnDir];
}


/**
 * Checks if the given row and column indices are within the limits of the matrix size.
 *
 * This function determines whether the given row and column indices are valid within the matrix,
 * which is defined by its size. It returns true if the indices are within the limits; otherwise, it returns false.
 *
 * @param int $rowIndex The index of the row.
 * @param int $columnIndex The index of the column.
 * @param int $matrixSize The size of the matrix.
 * @return bool Returns true if the row and column indices are within the limits of the matrix; otherwise, false.
 */
function isWithinMatrixLimits(int $rowIndex, int $columnIndex, int $matrixSize): bool
{
    return $rowIndex >= 0 && $rowIndex < $matrixSize && $columnIndex >= 0 && $columnIndex < $matrixSize;
}

echo "\r\n Heat loss: " . calculateHeatLoss();
