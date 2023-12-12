<?php

/**
 * Processes a spring character.
 *
 * @param string $character The character to process. Valid values are '.' and '#'.
 * @param int &$current A reference to the current count.
 * @param int &$offset A reference to the current offset.
 * @param array $damaged An array containing the damaged counts.
 *
 * @return bool Returns true if the character was successfully processed, false otherwise.
 *
 * @throws RuntimeException if an invalid character is passed.
 */
function processSpringCharacter(string $character, int &$current, int &$offset, array $damaged): bool {
    switch ($character) {
        case '.':
            if ($current > 0) {
                if ($current !== $damaged[$offset]) {
                    return false;
                }
                $current = 0;
                ++$offset;
            }
            break;
        case '#':
            ++$current;
            if ($offset >= count($damaged) || $current > $damaged[$offset]) {
                return false;
            }
            break;
        default:
            throw new RuntimeException('Invalid character: ' . $character);
    }
    return true;
}

/**
 * Determines if an arrangement is possible based on the given springs and damaged array.
 *
 * @param string $springs The string representing the springs.
 * @param array $damaged The array containing the damaged positions.
 *
 * @return bool Returns true if the arrangement is possible, false otherwise.
 */
function isArrangementPossible(string $springs, array $damaged): bool {
    $current = 0;
    $offset = 0;
    foreach (str_split($springs) as $char) {
        if (!processSpringCharacter($char, $current, $offset, $damaged)) {
            return false;
        }
    }
    return ($current === 0 || $current === $damaged[$offset]) && $offset === count($damaged) - ($current > 0 ? 1 : 0);
}

/**
 * Gets all possible arrangements of a string with "?" character replaced by either "." or "#".
 *
 * @param string $springs The input string containing "?" characters.
 * @param array $damaged An array of integers representing the positions of damaged characters.
 *
 * @return array An array containing all possible arrangements.
 */
function getArrangements(string $springs, array $damaged): array {
    $index = strpos($springs, '?');
    if ($index === false) {
        return isArrangementPossible($springs, $damaged) ? [$springs] : [];
    }
    return array_merge(
        getArrangements(replaceChar($springs, '.', $index), $damaged),
        getArrangements(replaceChar($springs, '#', $index), $damaged)
    );
}

/**
 * Replaces a character in a string at a specific index.
 *
 * @param string $str The input string.
 * @param string $char The character to replace with.
 * @param int $index The index at which to replace the character.
 *
 * @return string The updated string with the character replaced.
 */
function replaceChar(string $str, string $char, int $index): string {
    return substr_replace($str, $char, $index, 1);
}

/**
 * Calculates the total number of arrangements based on the contents of a file.
 *
 * @param string $filePath The path to the file containing the arrangements data.
 *
 * @return int The total number of arrangements.
 */
function totalArrangements(string $filePath): int {
    $sum = 0;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        [$springs, $damagedString] = explode(' ', $line);
        $damaged = array_map('intval', explode(',', $damagedString));
        $sum += count(getArrangements($springs, $damaged));
    }
    return $sum;
}

$filePath = 'input.txt';
echo "Total arrangements: " . totalArrangements($filePath);
