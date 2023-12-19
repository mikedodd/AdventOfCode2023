<?php

const FILEPATH = 'input.txt';
/**
 * Parses a file containing workflow data and returns an array of parsed workflows.
 *
 * @param string $line
 * @return array An associative array containing the parsed workflows. The keys are the workflow IDs and the values are arrays of rules.
 * Each rule is represented as an array containing the following items:
 *     - The name of the rule.
 *     - The comparison operator (either "<" or ">").
 *     - The rule value.
 */
function parseWorkflowLine(string $line): array {
    [$id, $rules] = preg_split("/[{}]/", $line, -1, PREG_SPLIT_NO_EMPTY);
    $rulesArray = explode(",", $rules);
    $parsedRules = array_map(fn($rule) => preg_split("/(?:([<>])|[:])/", $rule, 3, PREG_SPLIT_DELIM_CAPTURE), $rulesArray);

    return [$id => $parsedRules];
}

/**
 * Parses workflows from a file handle and returns an array of workflows.
 *
 * @param resource $fileHandle The file handle to read workflows from.
 *
 * @return array The array of parsed workflows.
 */
function parseWorkflows($fileHandle): array {
    $workflows = [];

    while (!feof($fileHandle) && $line = trim(fgets($fileHandle))) {

        if (empty($line)) {
            break;
        }

        $workflows += parseWorkflowLine($line);
    }

    return $workflows;
}


/**
 * Extracts values from each item in the given part using the specified delimiter.
 *
 * @param array $part The part containing the items to extract values from.
 *
 * @return array The extracted values from each item in the part.
 */
function extractValuesFromPart(array $part): array
{
    return array_map(fn($item) => explode("=", $item)[1], $part);
}

/**
 * Parses parts from a file handle and returns an array of parsed parts.
 *
 * @param resource $fileHandle The file handle to read from.
 * @return array The array of parsed parts, where each part is an associative
 *               array with keys 'x', 'm', 'a', 's' representing the extracted values.
 */
function parseParts($fileHandle): array
{
    $parts = [];
    while (!feof($fileHandle) && $line = trim(fgets($fileHandle))) {
        $part = explode(",", trim($line, "{}"));
        $part = extractValuesFromPart($part);
        $parts[] = array_combine(['x', 'm', 'a', 's'], $part);
    }
    return $parts;
}

/**
 * Processes a part based on a set of workflows.
 *
 * @param array $part The part to be processed.
 * @param array $workflows The set of workflows to apply on the part.
 * @return int The result of processing the part.
 */
function processPart(array $part, array $workflows): int {
    $currentWorkflow = 'in';
    while ($currentWorkflow !== 'A' && $currentWorkflow !== 'R') {
        $currentWorkflow = processWorkflow($part, $workflows, $currentWorkflow);
        if ($currentWorkflow === 'A') {
            return array_sum($part);
        }
    }
    return 0;
}

/**
 * Processes the workflow based on the provided part, workflows, and current workflow.
 *
 * @param array $part The part containing the necessary information for processing the workflow.
 * @param array $workflows The array of workflows containing rules to evaluate.
 * @param string $currentWorkflow The identifier of the current workflow to be processed.
 *
 * @return string The identifier of the next workflow to be processed.
 */
function processWorkflow(array $part, array $workflows, string $currentWorkflow): string {
    foreach ($workflows[$currentWorkflow] as $rule) {
        if (evaluateRule($part, $rule)) {
            return end($rule);
        }
    }
    return $currentWorkflow;
}

/**
 * Evaluates a rule against a given part.
 *
 * @param array $part An associative array representing a part.
 * @param array $rule An array representing a rule.
 * @return bool Returns true if the rule is satisfied, false otherwise.
 *
 * The $part array should have keys corresponding to the ratings and values representing the rating values.
 * The $rule array should have the following items:
 *     - The rating key to compare.
 *     - The comparison operator (either "<" or ">").
 *     - The numeric value to compare against.
 */
function evaluateRule(array $evaluationValues, array $evaluationRule): bool {
    if (count($evaluationRule) === 1) {
        return true;
    }

    [$rating, $op, $threshold] = $evaluationRule;

    $value = $evaluationValues[$rating] ?? null;

    if ($value === null) return false;

    return ($op === '>' && $value > $threshold) || ($op === '<' && $value < $threshold);
}

$fileHandle = fopen(FILEPATH, "r");
$workflows = parseWorkflows($fileHandle);
$parts = parseParts($fileHandle);
$totalSum = array_reduce($parts, fn($carry, $part) => $carry + processPart($part, $workflows), 0);

echo "Total sum of ratings for accepted parts: {$totalSum}\n";
