<?php

const FILENAME = 'input.txt';
const DIRECTIONS = [[0, 1], [0, -1], [1, 0], [-1, 0]];

/**
 * The main function of the program.
 *
 * This function performs the main operations of the program. It parses a path file to obtain a grid matrix,
 * sets the source and destination cells, initializes the initial states, initializes the priority queue,
 * processes the priority queue to calculate the heat loss, and finally, displays the heat loss.
 *
 * @return void
 */
function calcualteHeatLoss(): void
{
    list($matrix, $matrixSize) = parsePathToGrid(FILENAME);
    $sourceCell = [0, 0];
    $destinationCell = [$matrixSize - 1, $matrixSize - 1];

    $initialStates = getInitialStates($matrix, $sourceCell);
    $priorityQueue = initializeQueue($initialStates);
    $heatLoss = processQueue($priorityQueue, $matrix, $matrixSize, $destinationCell);

    echo "\r\n HEAT LOSS:" . $heatLoss . "\n";

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
 * Processes the queue to find the shortest path to the destination cell.
 *
 * @param object $queue The queue containing the states to process.
 * @param array $matrix The matrix representing the grid.
 * @param int $matrixSize The size of the matrix.
 * @param array $destinationCell The coordinates of the destination cell.
 * @return int|null The heat loss of the shortest path to the destination cell, or null if no path is found.
 */
function processQueue(object $queue, array $matrix, int $matrixSize, array $destinationCell): ?int
{
    $visitedStates = [];
    while (!$queue->isEmpty()) {
        echo count($queue) . "\r\n";

        list($heatLoss, list($row, $column), list(list($rowDir, $columnDir), $numOfDirs)) = $queue->extract();

        if ([$row, $column] == $destinationCell) {
            return $heatLoss;
        }

        if (!isStateVisited($visitedStates, $row, $column, $rowDir, $columnDir, $numOfDirs)) {
            $visitedStates[] = serialize([[$row, $column], [[$rowDir, $columnDir], $numOfDirs]]);
            processCurrentState($queue, $matrix, $matrixSize, $row, $column, $rowDir, $columnDir, $numOfDirs, $heatLoss);
        }
    }
    return null;
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
 * @param int $numOfDirs The number of directions visited in the current state.
 * @param float $heatLoss The heat loss for each state.
 * @return void
 */
function processCurrentState(SplPriorityQueue &$queue, array $matrix, int $matrixSize, int $row, int $column, int $rowDir, int $columnDir, int $numOfDirs, float $heatLoss): void
{
    if ($numOfDirs < 3) {
        enqueueIfValid($queue, $matrix, $matrixSize, $row + $rowDir, $column + $columnDir, $rowDir, $columnDir, $numOfDirs + 1, $heatLoss);
    }
    foreach (DIRECTIONS as list($newRowDir, $newColumnDir)) {
        if (shouldEnqueueDirection($rowDir, $columnDir, $newRowDir, $newColumnDir)) {
            enqueueIfValid($queue, $matrix, $matrixSize, $row + $newRowDir, $column + $newColumnDir, $newRowDir, $newColumnDir, 1, $heatLoss);
        }
    }
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
 * Enqueues a direction if it is valid based on the current position and matrix conditions.
 *
 * @param SplPriorityQueue $queue The priority queue to enqueue the direction.
 * @param array[][] $matrix The matrix containing the heat loss values.
 * @param int $matrixSize The size of the matrix.
 * @param int $newRow The new row position.
 * @param int $newColumn The new column position.
 * @param int $newRowDir The new row direction.
 * @param int $newColumnDir The new column direction.
 * @param int $newNumOfDirs The new number of directions.
 * @param int $currentHeatLoss The current heat loss.
 *
 * @return void
 */
function enqueueIfValid(SplPriorityQueue &$queue, array $matrix, int $matrixSize, int $newRow, int $newColumn, int $newRowDir, int $newColumnDir, int $newNumOfDirs, int $currentHeatLoss): void
{
    if (isWithinMatrixLimits($newRow, $newColumn, $matrixSize)) {
        $newHeatLoss = $currentHeatLoss + $matrix[$newRow][$newColumn];
        $queue->insert([$newHeatLoss, [$newRow, $newColumn], [[$newRowDir, $newColumnDir], $newNumOfDirs]], -$newHeatLoss);
    }
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
/**
 * Parses a given path to a grid.
 *
 * @param string $path The path to the file containing the grid.
 *
 * @return array Returns an array containing the parsed grid(matrix) and its size.
 */
function parsePathToGrid(string $path): array
{
    $file = file_get_contents($path);
    $matrix = array_map(function($row) { return array_map('intval', str_split(trim($row))); }, explode("\n", $file));
    return [$matrix, count($matrix)];
}

calcualteHeatLoss();
