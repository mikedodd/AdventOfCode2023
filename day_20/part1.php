<?php

/**
 * Loads the contents of the input file into an array.
 *
 * @param string $fileName The name of the input file to load.
 *
 * @return array The contents of the input file in an array, with each element representing a line in the file.
 *
 * @throws Exception If the input file cannot be loaded.
 */
function loadInputFile(string $fileName): array
{
    return file($fileName, FILE_IGNORE_NEW_LINES);
}

/**
 * Parses the given task input and returns an associative array.
 *
 * @param array $taskInput The input array containing task lines.
 * @return Generator Returns a generator that yields key-value pairs.
 */
function parse(array $taskInput): Generator
{
    foreach ($taskInput as $line) {
        $parts = explode(' -> ', $line);
        yield $parts[0] => explode(', ', $parts[1]);
    }
}

/**
 * Prepare linked modules.
 *
 * @param array &$modules The array of modules.
 * @param array &$linkedModules The array to store the linked modules.
 * @param string &$moduleName The name of the module being processed.
 * @return void
 */
function prepare(array &$modules, array &$linkedModules, string &$moduleName): void
{
    foreach (array_keys($linkedModules) as $moduleName) {
        foreach ($modules as $name => $module) {
            if (in_array($moduleName, $module)) {
                $linkedModules[$moduleName][$name] = false;
            }
        }
    }
}

/**
 * Prepare module details.
 *
 * @param array $lines The array of module names and their linked modules.
 * @param array $moduleDetails The array to store the module names and their details.
 * @param array $linkedModules The array to store the linked modules.
 * @param array $binaryStatus The array to indicate the binary status of modules.
 * @return void
 */
function prepareModuleDetails(array $lines, array &$moduleDetails, array &$linkedModules, array &$binaryStatus): void
{
    foreach ($lines as $moduleName => $linkedModule) {
        if ($moduleName[0] === '%') {
            $binaryStatus[substr($moduleName, 1)] = false;
            $moduleDetails[substr($moduleName, 1)] = $linkedModule;
            continue;
        }
        if ($moduleName[0] === '&') {
            $linkedModules[substr($moduleName, 1)] = [];
            $moduleDetails[substr($moduleName, 1)] = $linkedModule;
            continue;
        }
        $moduleDetails[$moduleName] = $linkedModule;
    }
}

/**
 * Runs the given task input and calculates the result.
 *
 * @param mixed $taskInput The input data for the task.
 * @return int Returns the calculated result of the task.
 */
function run(mixed $taskInput): int
{
    $parsedInputLines = iterator_to_array(parse($taskInput));
    $binaryModules = [];
    $relatedModules = [];
    $moduleDetails = [];
    $currentModule = "";
    $trueIncrement = $falseIncrement = 0;
    prepareModuleDetails($parsedInputLines, $moduleDetails, $relatedModules, $binaryModules);
    prepare($moduleDetails, $relatedModules, $currentModule);

    for ($buttonPressCount = 0; $buttonPressCount < 1000; $buttonPressCount++) {
        $updateResults = processEvents($moduleDetails, $relatedModules, $buttonPressCount, $trueIncrement, $falseIncrement, $binaryModules);

        if (in_array(true, $binaryModules, true) || in_array(true, array_map(fn($currentModule) => in_array(true, $currentModule, true), $relatedModules), true)) {
            continue;
        }

        $firstIndexValue = $updateResults[$buttonPressCount + 1];
        $moduloIndexValue = $updateResults[1000 % ($buttonPressCount + 1)];
        return (int)((1000 / ($buttonPressCount + 1)) * 1000 * $firstIndexValue[0] * $firstIndexValue[1] + 1000 * $moduloIndexValue[0] * $moduloIndexValue[1]);
    }
    return $trueIncrement * $falseIncrement;
}

/**
 * Process events and update increment values based on the event logic.
 *
 * @param array $module The module array.
 * @param array $conjunctions The conjunctions array.
 * @param mixed $pressedButton The pressed button value.
 * @param int &$incrementIfTrue The increment value for true events.
 * @param int &$incrementIfFalse The increment value for false events.
 * @param array &$binaries The binaries array by reference.
 *
 * @return array The updated results array with increment values.
 */
function processEvents(array $module, array $conjunctions, mixed $pressedButton, int &$incrementIfTrue, int &$incrementIfFalse, array &$binaries)
{
    $events = [['broadcaster', false, 'button']];

    while ($events) {
        list($where, $impulse, $_from) = array_shift($events);

        if ($impulse) {
            $incrementIfTrue++;
        } else {
            $incrementIfFalse++;
        }

        if ($where === 'button') {
            $events[] = ['broadcaster', false, $where];
        } elseif ($where === 'broadcaster') {
            foreach ($module[$where] as $w) {
                $events[] = [$w, $impulse, $where];
            }
        } elseif (isset($binaries[$where])) {
            if (!$impulse) {
                $nextImpulse = !$binaries[$where];
                foreach ($module[$where] as $w) {
                    $events[] = [$w, $nextImpulse, $where];
                }
                $binaries[$where] = !$binaries[$where];
            }
        } elseif (isset($conjunctions[$where])) {
            $conjunctions[$where][$_from] = $impulse;

            $nextImpulse = !(count(array_filter($conjunctions[$where], fn($v) => $v)) === count($conjunctions[$where]));
            foreach ($module[$where] as $w) {
                $events[] = [$w, $nextImpulse, $where];
            }
        }
    }

    $results[$pressedButton + 1] = [$incrementIfTrue, $incrementIfFalse];

    return $results;

}

$taskInput = loadInputFile("input.txt");
$result = run($taskInput);

echo "Result: " . $result . "\n";
