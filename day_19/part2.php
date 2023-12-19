<?php

const FILEPATH = 'input.txt';

/**
 * Parses a file containing workflows and returns an associative array
 * with workflow IDs as keys and an array of rules as values.
 *
 * @param resource $filePointer The file pointer to the workflows file.
 * @return array The associative array of workflow IDs and rules.
 */
function parseWorkflows($filePointer): array
{
    $workflows = [];
    while (!feof($filePointer) && $line = trim(fgets($filePointer))) {
        [$id, $rules] = preg_split("/[{}]/", $line, -1, PREG_SPLIT_NO_EMPTY);
        $rulesArray = explode(",", $rules);
        $rulesArray = array_map(fn($rule) => preg_split("/(?:([<>])|[:])/", $rule, 3, PREG_SPLIT_DELIM_CAPTURE), $rulesArray);
        $workflows[$id] = $rulesArray;
    }
    return $workflows;
}

/**
 * Parses a file containing parts and returns an array of parts.
 *
 * @param resource $filePointer The file pointer to the parts file.
 * @return array The array of parts with 'x', 'm', 'a', 's' as keys and their values as values.
 */
function parseParts($filePointer): array
{
    $parts = [];
    while (!feof($filePointer) && $line = trim(fgets($filePointer))) {
        $part = explode(",", trim($line, "{}"));
        $part = array_map(fn($item) => explode("=", $item)[1], $part);
        $part = array_combine(['x', 'm', 'a', 's'], $part);
        $parts[] = $part;
    }
    return $parts;
}

/**
 * Calculates the range based on the given operator, number, range start, and range end.
 *
 * @param string $operator The operator to use for calculating the range. Possible values are '>', '<', '>=', '<='.
 * @param int $number The number to compare with the range start and end.
 * @param int $rangeStart The starting value of the range.
 * @param int $rangeEnd The ending value of the range.
 * @return array The calculated range start and end values as an array.
 */
function calculateRange(string $operator, int $number, int $rangeStart, int $rangeEnd): array
{
    switch ($operator) {
        case '>':
            $rangeStart = max($rangeStart, $number + 1);
            break;
        case '<':
            $rangeEnd = min($rangeEnd, $number - 1);
            break;
        case '>=':
            $rangeStart = max($rangeStart, $number);
            break;
        case '<=':
            $rangeEnd = min($rangeEnd, $number);
            break;
    }
    return [$rangeStart, $rangeEnd];
}

/**
 * Calculates the range based on the given rating and updates the ranges array.
 *
 * @param string $rating The rating used to determine the range.
 * @param string $operator The operator used to calculate the range.
 * @param mixed $number The number used in the calculation.
 * @param array $ranges The array of ranges to be updated.
 * @return array The updated
 */
function calculateRangeBasedOnRating(string $rating, string $operator, mixed $number, array $ranges): array
{
    $ranges[$rating] = calculateRange($operator, $number, $ranges[$rating][0], $ranges[$rating][1]);

    return flattenArray($ranges);
}

/**
 * Flattens a multi-dimensional array into a single-dimensional array.
 *
 * @param array $inputArray The input array to be flattened.
 * @return array The flattened array.
 */
function flattenArray(array $inputArray): array
{
    $flattenedArray = [];
    foreach ($inputArray as $subArray) {
        foreach ($subArray as $value) {
            $flattenedArray[] = $value;
        }
    }
    return $flattenedArray;
}


/**
 * Determines if the iteration should be skipped based on given workflow ID and ranges.
 *
 * @param string $workflowId The ID of the workflow.
 * @param array $ranges The ranges for different values.
 *
 * @return bool Returns true if the iteration should be skipped, otherwise returns false.
 */
function shouldSkipIteration(string $workflowId, array $ranges): bool
{
    if ($workflowId == 'R') {
        return true;
    }

    return isFirstGreaterThanSecond($ranges, ['x', 'm', 'a', 's']);
}

/**
 * Determines if the first value of a range is greater than the second value for each key in the given keys array.
 *
 * @param array $ranges An associative array containing ranges for different keys.
 * @param array $keys An array of keys for which the ranges need to be evaluated.
 *
 * @return bool Returns true if the first value is greater than the second value for any key, otherwise returns false.
 */
function isFirstGreaterThanSecond(array $ranges, array $keys): bool
{
    foreach ($keys as $key) {
        if ($ranges[$key][0] > $ranges[$key][1]) {
            return true;
        }
    }
    return false;
}

/**
 * Calculates the result of part 2 based on the given ranges.
 *
 * @param array $ranges The ranges for different values.
 *
 * @return int The calculated result of part 2.
 */
function calculatePart2(array $ranges): int
{
    $result = 1;
    foreach($ranges as $range) {
        $result *= calculateRangeDifference($range);
    }
    return $result;
}

/**
 * Calculates the range difference based on an associative array
 * @param  array  $range  An array containing two values representing a range
 * @return int The calculated difference
 */
function calculateRangeDifference(array $range): int {
    return $range[1] - $range[0] + 1;
}
/**
 * Calculates the total count of items based on the given workflows and queue.
 *
 * @param array $workflows The array of workflows.
 * @param array $queue The queue of parameters for each iteration.
 * @return int The total count of items.
 */
function countItems(array $workflows, array $queue): int
{
    $part2Count = 0;

    while (count($queue)) {
        [$workflowId, $param1Start, $param1End, $param2Start, $param2End, $param3Start, $param3End, $param4Start, $param4End] = array_pop($queue);

        $ranges = [
            "x" => [$param1Start, $param1End],
            "m" => [$param2Start, $param2End],
            "a" => [$param3Start, $param3End],
            "s" => [$param4Start, $param4End],
        ];

        if (shouldSkipIteration($workflowId, $ranges)) {
            continue;
        }

        if ($workflowId == 'A') {
            $part2Count += calculatePart2($ranges);
            continue;
        }

        foreach ($workflows[$workflowId] as $rule) {
            if (count($rule) == 1) {
                $queue[] = array_merge([$rule[0]], [$param1Start, $param1End, $param2Start, $param2End, $param3Start, $param3End, $param4Start, $param4End]);
                break;
            }

            [$rating, $operation, $number, $destination] = $rule;

            $ranges = [
                "x" => [$param1Start, $param1End],
                "m" => [$param2Start, $param2End],
                "a" => [$param3Start, $param3End],
                "s" => [$param4Start, $param4End],
            ];

            $paramsAfterSplit = calculateRangeBasedOnRating($rating, $operation, $number, $ranges);
            $queue[] = array_merge([$destination], $paramsAfterSplit);

            $operation = ($operation == '>' ? '<=' : '>=');
            $newParams = calculateRangeBasedOnRating($rating, $operation, $number, $ranges);

            [$param1Start, $param1End, $param2Start, $param2End, $param3Start, $param3End, $param4Start, $param4End] = $newParams;
        }
    }

    return $part2Count;
}

$queue = [['in', 1, 4000, 1, 4000, 1, 4000, 1, 4000]];

$filePointer = fopen(FILEPATH, "r");
$workflows = parseWorkflows($filePointer);
$parts = parseParts($filePointer);
$part2 = countItems($workflows, $queue);

echo "Part 2: {$part2}\n";
