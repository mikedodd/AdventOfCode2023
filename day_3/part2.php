<?php

/**
 * Calculates the sum of product of numbers adjacent to gears in a schematic.
 *
 * @param string $filePath Path to the file containing the schematic.
 * @return int The sum of the products of adjacent gear numbers.
 */
function sumOfGearNumberProducts(string $filePath): int
{
    // Read the file and split into lines, ignoring new lines
    $schematic = file($filePath, FILE_IGNORE_NEW_LINES);
    $totalSum = 0;

    // Store gear-related numbers
    $gearRelatedNumbers = [];

    // Clean the schematic by replacing non-gear characters with a placeholder
    foreach ($schematic as $lineIndex => $line) {
        $schematic[$lineIndex] = preg_replace('/[^0-9*]/', '.', $line);
    }

    // Extract numbers that are adjacent to gears
    foreach ($schematic as $rowIndex => $line) {
        for ($colIndex = 0; $colIndex < strlen($line); $colIndex++) {
            if (is_numeric($line[$colIndex])) {
                $number = $line[$colIndex];
                $nextIndex = $colIndex + 1;

                // Build the entire number if it's more than one digit
                while ($nextIndex < strlen($line) && is_numeric($line[$nextIndex])) {
                    $number .= $line[$nextIndex];
                    $nextIndex++;
                }

                // Check if this number is adjacent to a gear symbol
                $gearPosition = findAdjacentGear($schematic, $rowIndex, $colIndex, $nextIndex - 1);
                if ($gearPosition) {
                    $gearRelatedNumbers[$gearPosition][] = (int)$number;
                }

                // Move the column index to the end of the current number
                $colIndex = $nextIndex - 1;
            }
        }
    }

    // Calculate the sum of gear number products
    foreach ($gearRelatedNumbers as $numbers) {
        if (count($numbers) == 2) {
            $totalSum += $numbers[0] * $numbers[1];
        }
    }

    return $totalSum;
}

/**
 * Finds if a number is adjacent to a gear symbol and returns its position.
 *
 * @param array $schematic The schematic as an array of strings.
 * @param int $row Row index of the current number.
 * @param int $startCol Start column index of the current number.
 * @param int $endCol End column index of the current number.
 * @return string|null Position of the adjacent gear or null if not found.
 */
function findAdjacentGear(array $schematic, int $row, int $startCol, int $endCol): ?string
{
    for ($col = $startCol; $col <= $endCol; $col++) {
        $gearPosition = checkForAdjacentGear($schematic, $row, $col);
        if ($gearPosition) {
            return $gearPosition;
        }
    }
    return null;
}

/**
 * Checks for a gear symbol adjacent to a specific position in the schematic.
 *
 * @param array $schematic The schematic as an array of strings.
 * @param int $rowIndex Row index of the current position.
 * @param int $colIndex Column index of the current position.
 * @return string|null Position of the adjacent gear or null if not found.
 */
function checkForAdjacentGear(array $schematic, int $rowIndex, int $colIndex): ?string
{
    $adjacentOffsets = [
        [-1, -1], [-1, 0], [-1, 1],
        [0, -1], [0, 1],
        [1, -1], [1, 0], [1, 1]
    ];

    foreach ($adjacentOffsets as $offset) {
        $adjacentRow = $rowIndex + $offset[0];
        $adjacentCol = $colIndex + $offset[1];

        if (isset($schematic[$adjacentRow][$adjacentCol])) {
            $adjacentChar = $schematic[$adjacentRow][$adjacentCol];
            if (!is_numeric($adjacentChar) && $adjacentChar !== '.' && $adjacentChar !== ' ') {
                return $adjacentRow . "-" . $adjacentCol;
            }
        }
    }

    return null;
}

$filePath = 'input.txt';
echo "What is the sum of all of the gear ratios in the engine schematic: " . sumOfGearNumberProducts($filePath) . "\n";