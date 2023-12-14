<?php
ini_set("memory_limit", "-1");

const FILEPATH = 'input.txt';
const CYCLES = 1000000000;
const DIRECTIONS = ['N', 'W', 'S', 'E'];
/**
 * Reads the platform's initial state from the file.
 *
 * @param string $filePath The path to the input file.
 * @return array The initial state of the platform.
 */
function readInitialState(string $filePath): array
{
    $content = file($filePath, FILE_IGNORE_NEW_LINES);
    return array_map('str_split', $content);
}

/**
 * Moves the rounded rocks in the specified direction.
 *
 * @param array $pattern The current state of the platform.
 * @param string $direction The direction to move the rocks ('N', 'W', 'S', 'E').
 */
function moveRocks(array &$pattern, string $direction): void
{
    $height = count($pattern);
    $width = count($pattern[0]);

    switch ($direction) {
        case 'N':
            for ($x = 0; $x < $width; $x++) {
                for ($y = 1; $y < $height; $y++) {
                    while ($y > 0 && $pattern[$y][$x] == 'O' && $pattern[$y - 1][$x] == '.') {
                        $pattern[$y - 1][$x] = 'O';
                        $pattern[$y][$x] = '.';
                        $y--;
                    }
                }
            }
            break;
        case 'W':
            for ($y = 0; $y < $height; $y++) {
                for ($x = 1; $x < $width; $x++) {
                    while ($x > 0 && $pattern[$y][$x] == 'O' && $pattern[$y][$x - 1] == '.') {
                        $pattern[$y][$x - 1] = 'O';
                        $pattern[$y][$x] = '.';
                        $x--;
                    }
                }
            }
            break;
        case 'S':
            for ($x = 0; $x < $width; $x++) {
                for ($y = $height - 1; $y >= 0; $y--) {
                    while ($y < $height - 1 && $pattern[$y][$x] == 'O' && $pattern[$y + 1][$x] == '.') {
                        $pattern[$y + 1][$x] = 'O';
                        $pattern[$y][$x] = '.';
                        $y++;
                    }
                }
            }
            break;
        case 'E':
            for ($y = 0; $y < $height; $y++) {
                for ($x = $width - 1; $x >= 0; $x--) {
                    while ($x < $width - 1 && $pattern[$y][$x] == 'O' && $pattern[$y][$x + 1] == '.') {
                        $pattern[$y][$x + 1] = 'O';
                        $pattern[$y][$x] = '.';
                        $x++;
                    }
                }
            }
            break;
    }
}

/**
 * Generates a hash representing the state of the given pattern.
 *
 * @param array $pattern The pattern to generate the state hash from.
 * @return string The generated hash representing the state of the pattern.
 */
function generateStateHash(array $pattern): string
{
    $hash = '';
    foreach ($pattern as $y => $row) {
        foreach ($row as $x => $value) {
            if ($value == 'O') {
                $hash .= "{$y},{$x};";
            }
        }
    }
    return $hash;
}

/**
 * Calculates the load based on the given pattern.
 *
 * @param array $pattern The pattern to calculate the load from.
 * @return int The calculated load value.
 */
function calculateLoad(array $pattern): int
{
    $totalLoad = 0;
    $height = count($pattern);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < count($pattern[$y]); $x++) {
            if ($pattern[$y][$x] == 'O') {
                $totalLoad += $height - $y;
            }
        }
    }

    return $totalLoad;
}


/**
 * Tilts the given pattern in all directions.
 *
 * @param array $pattern The pattern to be tilted. This parameter is passed by reference, meaning it
 *                       will be modified directly.
 *
 * @return void
 */
function tiltPattern(array &$pattern): void
{
    foreach (DIRECTIONS as $direction) {
        moveRocks($pattern, $direction);
    }
}

/**
 * Calculates the load after a certain number of cycles on the platform.
 *
 * @param array $pattern The initial state of the platform.
 * @param int $cycles The number of cycles to simulate.
 * @return int The final load on the platform after the specified number of cycles.
 */
function calculateLoadAfterCycles(array $pattern, int $cycles): int
{
    $seenStates = [];
    $revCache = [];
    $cycle = 0;
    $start = 0;
    $period = 0;

    while ($cycle < $cycles) {
        $hash = generateStateHash($pattern);

        if (isset($seenStates[$hash])) {
            if ($start == 0) {
                $start = $cycle;
                $seenStates = [];
            } else {
                $period = $cycle - $start - 1;
                break;
            }
        } else {
            $seenStates[$hash] = $cycle;
            $revCache[$cycle] = $pattern;
        }

        tiltPattern($pattern);
        $cycle++;
    }

    if ($period > 0) {
        $pattern = $revCache[$start + ($cycles - $start) % $period];
        return calculateLoad($pattern);
    }

    return calculateLoad($pattern);
}

$initialState = readInitialState(FILEPATH);
echo "Total Load after " . CYCLES . " cycles: " . calculateLoadAfterCycles($initialState, CYCLES) . PHP_EOL;
