<?php
/**
 * Reads a platform from a file and returns it as a multidimensional array.
 *
 * @param string $filePath The path of the file to read the platform from.
 * @return array The platform as a multidimensional array.
 */
function readPlatformFromFile(string $filePath): array {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    return array_map('str_split', $lines);
}

/**
 * Tilt the platform towards the north.
 *
 * @param array $platform The platform to tilt.
 * @return array The tilted platform.
 */
function tiltPlatformNorth(array $platform): array {
    $rowCount = count($platform);
    $columnCount = count($platform[0]);

    for ($col = 0; $col < $columnCount; $col++) {
        for ($row = 0; $row < $rowCount; $row++) {
            if ($platform[$row][$col] === 'O') {
                $newRow = $row;
                while ($newRow > 0 && $platform[$newRow - 1][$col] === '.') {
                    $newRow--;
                }
                if ($newRow !== $row) {
                    $platform[$newRow][$col] = 'O';
                    $platform[$row][$col] = '.';
                }
            }
        }
    }

    return $platform;
}

/**
 * Calculates the total load of a given platform.
 *
 * @param array $platform The platform to calculate the total load for.
 * @return int The total load of the platform.
 */
function calculateTotalLoad(array $platform): int {
    $totalLoad = 0;
    $rowCount = count($platform);

    foreach ($platform as $rowIndex => $row) {
        $loadFactor = $rowCount - $rowIndex;
        $roundedRocksCount = count(array_filter($row, fn($cell) => $cell === 'O'));
        $totalLoad += $roundedRocksCount * $loadFactor;
    }

    return $totalLoad;
}

// Read platform from file
$filePath = 'input.txt';
$platform = readPlatformFromFile($filePath);

// Tilt platform and calculate load
$tiltedPlatform = tiltPlatformNorth($platform);
$totalLoad = calculateTotalLoad($tiltedPlatform);

echo "Total load on the north support beams: " . $totalLoad . PHP_EOL;
//107142