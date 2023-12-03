<?php

/**
 * Reads the engine schematic and calculates the sum of all part numbers.
 *
 * @param string $filePath Path to the file containing the engine schematic.
 * @return int The sum of all part numbers.
 */
function sumOfPartNumbers(string $filePath): int
{
    $schematic = file($filePath, FILE_IGNORE_NEW_LINES);
    $sum = 0;

    for ($i = 0; $i < count($schematic); $i++) {
        for ($j = 0; $j < strlen($schematic[$i]); $j++) {
            if (is_numeric($schematic[$i][$j])) {
                // Start building the number from this digit
                $number = $schematic[$i][$j];
                $k = $j + 1;

                // Continue while the next characters are numeric
                while ($k < strlen($schematic[$i]) && is_numeric($schematic[$i][$k])) {
                    $number .= $schematic[$i][$k];
                    $k++;
                }

                // Check if the number is adjacent to a symbol
                if (isNumberAdjacentToSymbol($schematic, $i, $j, $k - 1)) {
                    $sum += (int) $number;
                }

                // Update the column index
                $j = $k - 1;
            }
        }
    }

    return $sum;
}

/**
 * Checks if any part of a number is adjacent to a symbol.
 *
 * @param array $schematic The engine schematic.
 * @param int $row The row index.
 * @param int $startCol The column index where the number starts.
 * @param int $endCol The column index where the number ends.
 * @return bool True if any part of the number is adjacent to a symbol, false otherwise.
 */
function isNumberAdjacentToSymbol(array $schematic, int $row, int $startCol, int $endCol): bool
{
    for ($col = $startCol; $col <= $endCol; $col++) {
        if (hasAdjacentSymbol($schematic, $row, $col)) {
            return true;
        }
    }
    return false;
}

/**
 * Checks if a cell has an adjacent symbol.
 *
 * @param array $schematic The engine schematic.
 * @param int $i The row index.
 * @param int $j The column index.
 * @return bool True if an adjacent symbol is found, false otherwise.
 */
function hasAdjacentSymbol(array $schematic, int $i, int $j): bool
{
    $adjacentOffsets = [
        [-1, -1], [-1, 0], [-1, 1],
        [0, -1],           [0, 1],
        [1, -1],  [1, 0],  [1, 1]
    ];

    foreach ($adjacentOffsets as $offset) {
        $di = $i + $offset[0];
        $dj = $j + $offset[1];

        if ($di >= 0 && $di < count($schematic) && $dj >= 0 && $dj < strlen($schematic[$di])) {
            $adjacentChar = $schematic[$di][$dj];
            if (!is_numeric($adjacentChar) && $adjacentChar !== '.' && $adjacentChar !== ' ') {
                return true;
            }
        }
    }

    return false;
}

$filePath = 'input.txt';
echo "Sum of all part numbers: " . sumOfGearRatios($filePath) . "\n";

// 537832