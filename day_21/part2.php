<?php

$infinity = PHP_INT_MAX;

/**
 * Reads a map from a file and returns it as a two-dimensional array.
 *
 * @return array The map as a two-dimensional array.
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
 * Solves for the distances of each cell in a grid starting from a specified cell.
 *
 * @param array $grid The grid structure.
 * @param int $gridRows The number of rows in the grid.
 * @param int $gridCols The number of columns in the grid.
 * @param int $startRow The starting row index.
 * @param int $startCol The starting column index.
 * @param int $infinity The maximum value indicating infinity.
 * @return array The distances of each cell from the starting cell.
 */
function solve(array $grid, int $gridRows, int $gridCols, int $startRow, int $startCol, int $infinity): array
{
    $dirs = 4;
    $dRow = [-1, 0, +1, 0];
    $dCol = [0, +1, 0, -1];

    $distances = array_fill(0, $gridRows, array_fill(0, $gridCols, $infinity));
    $queue = [];
    $queue[] = [$startRow, $startCol, 0];

    while (!empty($queue)) {
        [$row, $col, $dist] = array_shift($queue);

        if ($distances[$row][$col] !== $infinity) continue;
        $distances[$row][$col] = $dist;

        for ($dir = 0; $dir < $dirs; $dir++) {
            $nRow = $row + $dRow[$dir];
            $nCol = $col + $dCol[$dir];

            if ($nRow >= 0 && $nRow < $gridRows && $nCol >= 0 && $nCol < $gridCols && $grid[$nRow][$nCol] !== '#' && $distances[$nRow][$nCol] === $infinity) {
                $queue[] = [$nRow, $nCol, $dist + 1];
            }
        }
    }

    return $distances;
}

/**
 * Processes the rows of a map structure and calculates a result based on the given parameters.
 *
 * @param int $numberOfRows The number of rows in the map structure.
 * @param array $mapStructure The map structure.
 * @param int $numberOfColumns The number of columns in the map structure.
 * @param int $infinityIndicator The maximum value of coordinates indicating infinity.
 * @return mixed The calculated result.
 */
function processRows(int $numberOfRows, array $mapStructure, int $numberOfColumns, int $infinityIndicator): mixed
{
    for ($currentRow = 0; $currentRow < $numberOfRows; $currentRow++) {
        for ($currentColumn = 0; $currentColumn <$numberOfColumns; $currentColumn++) {
            if ($mapStructure[$currentRow][$currentColumn] === 'S') {
                break 2;
            }
        }
    }

    $calculatedSteps = 26501365;
    $isEvenOrOdd = $calculatedSteps % 2;
    $sizeOfMatrix = $numberOfRows;
    $directionMatrix = [];
    $halfSizeOfMatrix = intdiv($sizeOfMatrix, 2);

    for ($uIndex = 0; $uIndex < 3; $uIndex++) {
        for ($vIndex = 0; $vIndex < 3; $vIndex++) {
            $centeredRow = $halfSizeOfMatrix * $uIndex;
            $centeredColumn = $halfSizeOfMatrix * $vIndex;
            $directionMatrix[$uIndex][$vIndex] = solve($mapStructure, $numberOfRows, $numberOfColumns, $centeredRow, $centeredColumn, $infinityIndicator);
        }
    }

    $results = [0, 0];
    $fullSteps = intdiv($calculatedSteps, $sizeOfMatrix);
    foreach ([0, 1] as $bit) {
        foreach ($directionMatrix[1][1] as $lineInMatrix) {
            foreach ($lineInMatrix as $xCoordinate) {
                if ($xCoordinate < $infinityIndicator && $xCoordinate % 2 === $bit) {
                    $results[$bit]++;
                }
            }
        }
    }

    $solution = $results[$isEvenOrOdd];
    for ($iIndex = 1; $iIndex < $fullSteps; $iIndex++) {
        $solution += $results[($iIndex & 1) ^ $isEvenOrOdd] * $iIndex * 4;
    }

    return calculateResult($directionMatrix, $sizeOfMatrix, $halfSizeOfMatrix, $fullSteps, $solution);
}

/**
 * Calculate the result based on the grid distances and other parameters.
 *
 * @param array $gridDistances The grid distances.
 * @param int $gridSize The size of the grid.
 * @param int $halfGridSize Half of the grid size.
 * @param int $fullSteps The number of full steps.
 * @param int $initialResult The initial result.
 * @return int The calculated result.
 */
function calculateResult(array $gridDistances, int $gridSize, int $halfGridSize, int $fullSteps, int $initialResult): int
{
    $cornerTiles = 0;
    $cornerTiles += calculateTraversal($gridDistances, 0, 1, $gridSize - 1);
    $cornerTiles += calculateTraversal($gridDistances, 1, 0, $gridSize - 1);
    $cornerTiles += calculateTraversal($gridDistances, 2, 1, $gridSize - 1);
    $cornerTiles += calculateTraversal($gridDistances, 1, 2, $gridSize - 1);
    $lowEdgeTiles = 0;
    $lowEdgeTiles += calculateTraversal($gridDistances, 0, 0, $halfGridSize - 1);
    $lowEdgeTiles += calculateTraversal($gridDistances, 0, 2, $halfGridSize - 1);
    $lowEdgeTiles += calculateTraversal($gridDistances, 2, 0, $halfGridSize - 1);
    $lowEdgeTiles += calculateTraversal($gridDistances, 2, 2, $halfGridSize - 1);
    $lowEdgeTiles *= $fullSteps;
    $highEdgeTiles = 0;
    $highEdgeTiles += calculateTraversal($gridDistances, 0, 0, $halfGridSize + $gridSize - 1);
    $highEdgeTiles += calculateTraversal($gridDistances, 0, 2, $halfGridSize + $gridSize - 1);
    $highEdgeTiles += calculateTraversal($gridDistances, 2, 0, $halfGridSize + $gridSize - 1);
    $highEdgeTiles += calculateTraversal($gridDistances, 2, 2, $halfGridSize + $gridSize - 1);
    $highEdgeTiles *= ($fullSteps - 1);

    return $initialResult + $cornerTiles + $lowEdgeTiles + $highEdgeTiles;
}


/**
 * Calculate the number of traversals that meet certain conditions
 *
 * @param array $gridDistances The grid distances array
 * @param int $firstIndex The first index for accessing grid distances
 * @param int $secondIndex The second index for accessing grid distances
 * @param int $maxDistance The maximum distance for a traversal
 *
 * @return int The number of
 */
function calculateTraversal(array $gridDistances, int $firstIndex, int $secondIndex, int $maxDistance): int
{
    $parity = $maxDistance % 2;
    $count = 0;
    foreach ($gridDistances[$firstIndex][$secondIndex] as $line) {
        foreach ($line as $distance) {
            if ($distance <= $maxDistance && $distance % 2 === $parity) {
                $count++;
            }
        }
    }
    return $count;
}


$map = readMap();
$rows = count($map);
$cols = count($map[0]);

$reachablePlots = processRows($rows, $map, $cols, $infinity);

echo "Number of reachable plots in 64 steps: $reachablePlots\n";
