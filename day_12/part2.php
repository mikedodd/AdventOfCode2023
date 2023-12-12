<?php

const VALID_CHARS = ['?', '#'];
const FILEPATH = 'input.txt';


/**
 * Calculates the number of possible arrangements based on the springs and damaged array.
 *
 * @param string $springs The string representation of the springs.
 * @param array $damaged The array of damaged springs.
 * @param int|null $remaining The remaining value.
 * @return int The number of possible arrangements.
 */
function getCountOfPossibleValues(string $springs, array $damaged, ?int $remaining = null): int
{
    static $cache = [];
    $cacheKey = $springs . '-' . join('-', $damaged);
    if (isset($cache[$cacheKey])) {
        return $cache[$cacheKey];
    }
    $damagedAmount = array_shift($damaged);
    if ($damagedAmount === null || $damagedAmount < 1) {
        $index = stripos($springs, '#');
        if ($index !== false) {
            return 0;
        }
        return 1;
    }
    $remaining = calculateRemaining($remaining, $damagedAmount, $damaged);
    $springsLength = strlen($springs);

    return $cache[$cacheKey] = calculatePossibilities($springsLength, $damagedAmount, $remaining, $springs, $damaged);
}

/**
 * Calculates the number of valid combinations based on the given parameters.
 *
 * @param int $springsLength The length of the springs.
 * @param int $damagedAmount The amount of damaged springs.
 * @param int $remaining The number of remaining springs.
 * @param string $springs The string representation of the springs.
 * @param array $damaged The array of indices of the damaged springs.
 * @return int The number of valid combinations.
 */
function calculatePossibilities(int $springsLength, int $damagedAmount, int $remaining, string $springs, array $damaged): int
{
    $totalValidCombinations = 0;
    $countPossibleCombinations = $springsLength - $damagedAmount - $remaining + 1;

    for ($index = 0; $index < $countPossibleCombinations; ++$index) {
        list($isValidCombination, $countPossibleCombinations) = validateAndCountPossibilities($index, $damagedAmount, $springsLength, $springs, $countPossibleCombinations);
        if ($isValidCombination) {
            $isLastSpring = $index + $damagedAmount === $springsLength;
            if ($isLastSpring) {
                ++$totalValidCombinations;
            } else {
                $subSprings = substr($springs, $index + $damagedAmount + 1);
                $subCombinations = getCountOfPossibleValues($subSprings, $damaged, $remaining);
                $totalValidCombinations += $subCombinations;

            }

        }
    }
    return $totalValidCombinations;
}

/**
 * Calculates the remaining value based on the initial remaining value, damaged amount, and array of damaged values.
 *
 * @param int|null $remaining The initial remaining value. If null, it will be calculated as the sum of the damaged values.
 * @param int $damagedAmount The amount to be subtracted from the remaining value.
 * @param int[] $damaged The array of damaged values.
 * @return int The updated remaining value after the calculation.
 */
function calculateRemaining(?int $remaining, int $damagedAmount, array $damaged): int
{
    if ($remaining === null) {
        $remaining = array_sum($damaged);
    } else {
        $remaining -= $damagedAmount;
    }

    return $remaining;
}

/**
 * Checks if the given spring configuration is valid.
 *
 * @param int $startIdx The starting index of the configuration.
 * @param int $distance The distance between springs in the configuration.
 * @param int $springsLength The total length of the springs.
 * @param string $springs The string representation of the springs.
 * @return bool Whether the spring configuration is valid or not.
 */
function isValidSpringConfiguration(int $startIdx, int $distance, int $springsLength, string $springs): bool
{
    $isNextSpringValid = $startIdx + $distance >= $springsLength
        || $springs[$startIdx + $distance] === '.'
        || $springs[$startIdx + $distance] === '?';

    $isPrevSpringValid = $startIdx === 0
        || $springs[$startIdx - 1] === '.'
        || $springs[$startIdx - 1] === '?';

    $endIdx = min($springsLength, $startIdx + $distance);

    $isSubStringValid = isSubStringValid($springs, $startIdx, $endIdx);

    return $isNextSpringValid && $isPrevSpringValid && $isSubStringValid;
}


/**
 * Checks if the substring within the specified range of indices is valid.
 *
 * @param string $str The input string.
 * @param int $startIdx The starting index of the substring.
 * @param int $endIdx The ending index of the substring (exclusive).
 * @return bool True if the substring only contains valid characters, false otherwise.
 */
function isSubStringValid(string $str, int $startIdx, int $endIdx): bool
{
    for ($j = $startIdx; $j < $endIdx; ++$j) {
        if (!in_array($str[$j], VALID_CHARS)) {
            return false;
        }
    }

    return true;
}

/**
 * Calculates the maximum index for the given parameters.
 *
 * @param int $i The current index.
 * @param string $springs The springs string.
 * @param int $iMax The maximum index value.
 * @return int The calculated maximum index.
 */
function calculateMaxIndex(int $i, string $springs, int $iMax): int
{
    if ($springs[$i] === '#') {
        return min($i + 1, $iMax);
    } else {
        return $iMax;
    }
}

/**
 * Validates and counts the possibilities based on the given parameters.
 *
 * @param int $i The current index.
 * @param int $d The distance value.
 * @param int $springsLength The length of the springs string.
 * @param string $springs The springs string.
 * @param int $iMax The maximum index value.
 * @return array An array containing two values:
 *               - boolean: indicates whether the validation is valid or not.
 *               - int: the updated maximum index value.
 */
function validateAndCountPossibilities(int $i, int $d, int $springsLength, string $springs, int $iMax): array
{
    $valid = isValidSpringConfiguration($i, $d, $springsLength, $springs);
    $iMax = calculateMaxIndex($i, $springs, $iMax);

    return array($valid, $iMax);
}

/**
 * Calculates the total arrangements by reading lines from a file and
 * calling the "evaluateLineArrangements()" function on each line.
 *
 * @return int The total value after evaluating all line arrangements.
 */
function totalArrangements(): int
{
    $lines = file(FILEPATH, FILE_IGNORE_NEW_LINES);

    return array_reduce($lines, "evaluateLineArrangements", 0);
}

/**
 * Evaluates line arrangements based on the total and line input.
 *
 * @param int $total The total value.
 * @param string $line The line input.
 * @return int The updated total value after evaluating the line arrangements.
 */
function evaluateLineArrangements(int $total, string $line): int
{
    $lineParts = getFormattedLineParts($line);

    if (count($lineParts) !== 2) {
        return $total;
    }

    [$springSizes, $damagedSprings] = $lineParts;
    $springSizes = prepareSpringSizes($springSizes);
    $damagedSpringsArray = prepareDamagedSprings($damagedSprings);

    return $total + getCountOfPossibleValues($springSizes, $damagedSpringsArray);
}


/**
 * Prepares the spring sizes for further*/
function prepareSpringSizes(string $springs): string
{
    return repeatSeparatedString($springs, '?', 5);
}

/**
 * Repeats a string separated by a delimiter multiple times.
 *
 * @param string $string The string to repeat.
 * @param string $delimiter The delimiter to separate the repeated strings.
 * @param int $times The number of times to repeat the string.
 * @return string The resulting string after repeating the input string.
 */
function repeatSeparatedString(string $string, string $delimiter, int $times): string
{
    return implode($delimiter, array_fill(0, $times, $string));
}

/**
 * Prepares damaged springs.
 *
 * This function takes a string of damaged values and converts it into an array of integers representing the damaged springs.
 *
 * @param string $damagedValues A string containing the damaged values, separated by a comma (',').
 * @return int[] An array containing the damaged springs.
 */
function prepareDamagedSprings(string $damagedValues): array
{
    $valuesArray = explode(",", $damagedValues);
    $repeatedValuesArray = array_fill(0, 5, $valuesArray);
    return call_user_func_array('array_merge', $repeatedValuesArray);
}

/**
 * Returns an array of formatted line parts.
 *
 * @param string $line The input line to be processed.
 * @return string[] The array of formatted line parts.
 */
function getFormattedLineParts(string $line): array
{
    return array_map('trim', explode(' ', trim($line)));
}

echo "Total arrangements: " . totalArrangements();

//4232520187524
