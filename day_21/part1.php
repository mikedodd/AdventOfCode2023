<?php

/**
 * Read a map from a file and convert it into a 2D array
 *
 * @return array A 2D array representing the map
 */
function readMap(): array
{
    $map = [];

    $filePath = "input.txt";
    $fileHandle = fopen($filePath, "r");

    while ($line = fgets($fileHandle)) {
        $map[] = str_split(trim($line));
    }
    return $map;
}

/**
 * Perform breadth-first search (BFS) on a map to find the distances from a starting position
 *
 * @param array $map A 2D array representing the map
 * @param int $rows The number of rows in the map
 * @param int $cols The number of columns in the map
 * @return array A 2D array representing the distances from the starting position to each cell in the map
 */
function bfs(array $map, int $rows, int $cols): array
{
    $dirs = 4;
    $dRow = [-1,  0, +1,  0];
    $dCol = [ 0, +1,  0, -1];
    $infinity = PHP_INT_MAX;

    $distances = array_fill(0, $rows, array_fill(0, $cols, $infinity));
    $queue = [];

    // Find starting position and initialize queue
    foreach ($map as $row => $line) {
        foreach ($line as $col => $char) {
            if ($char === 'S') {
                $distances[$row][$col] = 0;
                $queue[] = ['row' => $row, 'col' => $col];
            }
        }
    }

    // BFS
    while ($queue) {
        $current = array_shift($queue);
        $curRow = $current['row'];
        $curCol = $current['col'];
        $curDist = $distances[$curRow][$curCol];

        for ($dir = 0; $dir < $dirs; $dir++) {
            $nRow = $curRow + $dRow[$dir];
            $nCol = $curCol + $dCol[$dir];

            if ($nRow >= 0 && $nRow < $rows && $nCol >= 0 && $nCol < $cols &&
                $map[$nRow][$nCol] !== '#' && $distances[$nRow][$nCol] === $infinity) {
                $distances[$nRow][$nCol] = $curDist + 1;
                $queue[] = ['row' => $nRow, 'col' => $nCol];
            }
        }
    }

    return $distances;
}

/**
 * Count the number of reachable plots within a certain number of steps
 *
 * @param array $distances A 2D array representing the distances from the starting position to each cell in the map
 * @param int $steps The maximum number of steps allowed
 * @return int The count of reachable plots
 */
function countReachablePlots(array $distances, int $steps): int
{
    $count = 0;
    foreach ($distances as $row) {
        foreach ($row as $dist) {
            if ($dist <= $steps && $dist % 2 === $steps % 2) {
                $count++;
            }
        }
    }
    return $count;
}

// Read map from stdin
$map = readMap();
$rows = count($map);
$cols = count($map[0]);

// Perform BFS
$distances = bfs($map, $rows, $cols);

// Count reachable plots in 64 steps
$steps = 64;
$reachablePlots = countReachablePlots($distances, $steps);

echo "Number of reachable plots in 64 steps: $reachablePlots\n";
