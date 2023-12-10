<?php

/**
 * Gets adjacent coordinates for a given position on the map.
 *
 * @param array $map The array representation of the map.
 * @param int $xCoordinate The x-coordinate of the current position.
 * @param int $yCoordinate The y-coordinate of the current position.
 * @return array Returns an array of adjacent coordinates.
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
 * Gets the initial position of the map explorer based on the map data.
 *
 * @param array $rows The array representation of the rows of the map.
 * @return array|null Returns an array of the initial position coordinates or null if not found.
 */
function getInitialPosition(array &$rows): ?array
{
    foreach ($rows as $rowIndex => $row) {
        $rows[$rowIndex] = str_split($row);
        $startPosition = array_search('S', $rows[$rowIndex]);
        if ($startPosition !== false) {
            return [$startPosition, $rowIndex];
        }
    }
    return null;
}

/**
 * Computes the number of moves required to navigate the map and reach the final position.
 *
 * @param string $mapData The map input.
 * @return int Returns the number of moves.
 */
function computeMapTraversal(string $mapData): int
{
    $rows = explode(PHP_EOL, $mapData);
    $initialPosition = getInitialPosition($rows);

    $positionsToExplore = getAdjacentCoordinates($rows, $initialPosition[0], $initialPosition[1]);
    $moveCount = 1;

    while (true) {
        foreach ($positionsToExplore as $index => $position) {
            $newPosition = getAdjacentCoordinates($rows, $position[0], $position[1]);
            $rows[$position[1]][$position[0]] = 'S';
            if (empty($newPosition)) {
                break 2; // Break out of both loops.
            }
            $positionsToExplore[$index] = $newPosition[0];
        }
        $moveCount++;
        if ($positionsToExplore[0] === $positionsToExplore[1]) {
            break;
        }
    }

    return $moveCount;
}

$mapInputData = file_get_contents('input.txt');
$numberOfMoves = computeMapTraversal($mapInputData);

echo "Number of moves: " . $numberOfMoves;