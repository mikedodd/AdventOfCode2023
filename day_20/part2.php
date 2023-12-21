<?php

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
 * Run the module processing algorithm.
 *
 * @param array $rawModuleData An array containing the raw module data.
 * @return GMP The final result calculated from the processed modules.
 */
function run(array $rawModuleData): GMP
{
    $parsedModules = iterator_to_array(parse($rawModuleData));
    $modules = [];

    foreach ($parsedModules as $moduleID => $connectedModules) {
        $moduleType = $moduleID[0];
        if ($moduleType != 'b') {
            $moduleID = substr($moduleID, 1);
        }
        $modules[$moduleID] = [
            $moduleType,
            $connectedModules,
            ($moduleType == '&' ? [] : 0)
        ];
    }

    foreach ($modules as $moduleID => [, $connectedModules,]) {
        foreach ($connectedModules as $connectedModuleID) {
            if (!isset($modules[$connectedModuleID])) {
                $modules[$connectedModuleID] = [$connectedModuleID, [], []];
            }
            if ($modules[$connectedModuleID][0] == '&' ) {
                $modules[$connectedModuleID][2][$moduleID] = 0;
            }
            if ($modules[$connectedModuleID][0] == $connectedModuleID) {
                $modules[$connectedModuleID][2][$moduleID] = 0;
            }
        }
    }

    return processModulesQueue($modules);
}


/**
 * Process a single iteration of the modules queue.
 *
 * @param array $modules An array of modules.
 * @param array $cycles An array of cycles.
 * @param array $found An array to store the found modules.
 * @param array $count An array to store the count of pulses.
 * @param int $iteration The current iteration number.
 * @return bool Returns true if all cycles are found and the final result needs to be calculated, false otherwise.
 */
function processQueueIteration(array &$modules, array &$cycles, array &$found, array &$count, int $iteration): bool {
    $queue = [["broadcaster", "button", 0]];

    while (count($queue)) {
        [$id, $from, $pulse] = array_shift($queue);
        $count[$pulse]++;

        if (!$pulse && isset($cycles[$id])) {
            if ($cycles[$id] > 0 && !isset($found[$id])) {
                $found[$id] = $iteration - $cycles[$id];
                if (count($found) == count($cycles)) {
                    return true; // Signal to calculate final result
                }
            } else {
                $cycles[$id] = $iteration;
            }
        }

        processModule($id, $from, $pulse, $modules, $queue);
    }

    return false; // Continue the iteration
}

/**
 * Calculate the final result from the found modules.
 *
 * @param array $found A reference to an array containing the found modules.
 * @return GMP The final result calculated from the found modules.
 */
function calculateFinalResult(array &$found): GMP {
    $part2 = array_shift($found);
    foreach ($found as $n) {
        $part2 = gmp_lcm($part2, $n);
    }
    return $part2;
}

/**
 * Process the modules queue.
 *
 * @param array $modules An array of modules.
 * @return GMP The final result calculated from the found modules.
 */
function processModulesQueue(array $modules): GMP {
    $count = [0, 0];
    $cycles = $modules[array_key_first($modules["rx"][2])][2];
    $found = [];

    for ($i = 0; ; $i++) {
        if (processQueueIteration($modules, $cycles, $found, $count, $i)) {
            return calculateFinalResult($found);
        }
    }
}


/**
 * Process the flip flop module by updating its state and adding connected modules to the processing queue.
 *
 * @param string $moduleID The ID of the flip flop module to be processed.
 */
function processFlipFlopModule(string $moduleID, int $signalPulse, array &$allModules, array &$processingQueue): void
{
    if ($signalPulse) {
        return;
    }

    $allModules[$moduleID][2] = !$allModules[$moduleID][2];
    $newPulse = $allModules[$moduleID][2];

    foreach ($allModules[$moduleID][1] as $targetModule) {
        $processingQueue[] = [$targetModule, $moduleID, $newPulse];
    }
}

/**
 * Process the conjunction module and update the allModules and processingQueue arrays.
 *
 * @param string $moduleID The ID of the conjunction module.
 * @param string $triggerID The ID of the trigger module.
 * @param int $signalPulse The signal pulse value.
 * @param array $allModules The array containing all the modules.
 * @param array $processingQueue The queue for processing the modules.
 */
function processConjunctionModule(string $moduleID, string $triggerID, int $signalPulse, array &$allModules, array &$processingQueue): void
{
    $allModules[$moduleID][2][$triggerID] = $signalPulse;

    if (!(array_sum($allModules[$moduleID][2]) == count($allModules[$moduleID][2]))) {
        $newPulse = true;
    } else {
        $newPulse = false;
    }

    foreach ($allModules[$moduleID][1] as $targetModule) {
        $processingQueue[] = [$targetModule, $moduleID, $newPulse];
    }
}

/**
 * Process a module based on its type and trigger.
 *
 * @param string $moduleID The ID of the module to process.
 * @param string $triggerID The ID of the trigger module.
 * @param int $signalPulse The signal pulse received by the module.
 * @param array &$allModules The array containing all modules and their data.
 * @param array &$processingQueue The queue for processing modules.
 * @return void
 */
function processModule(string $moduleID, string $triggerID, int $signalPulse, array &$allModules, array &$processingQueue): void
{
    $moduleType = $allModules[$moduleID][0];

    switch ($moduleType) {
        case "%":
            processFlipFlopModule($moduleID, $signalPulse, $allModules, $processingQueue);
            break;
        case "&":
            processConjunctionModule($moduleID, $triggerID, $signalPulse, $allModules, $processingQueue);
            break;
        default:
            // Propagate the pulse to children for other module types
            foreach ($allModules[$moduleID][1] as $targetModule) {
                $processingQueue[] = [$targetModule, $moduleID, $signalPulse];
            }
            break;
    }
}

$taskInput = loadInputFile("input.txt");
$result = run($taskInput);

echo "Part 2 Result: " . $result . "\n";