<?php

const INPUT_FILE = "input.txt";
const MAX_CONNECTION_COUNT = 4;

/**
 * Process connected components in the componentRelations array.
 *
 * This function counts the number of connections between the startingComponent and the secondaryComponent
 * in the componentRelations array.
 *
 * @param array $componentRelations An array representing the relationships between components.
 * @param string $startingComponent The starting component for the connections.
 * @param string $secondaryComponent The secondary component for the connections.
 * @return int The number of connections between the startingComponent and the secondaryComponent.
 */
function processConnectedComponents(array $componentRelations, string $startingComponent, string $secondaryComponent): int
{
    $connectionCount = 0;
    $visitedComponents = [$startingComponent];

    foreach ($componentRelations[$startingComponent] as $connectedComponent) {
        if ($connectedComponent == $secondaryComponent) {
            $connectionCount++;
            continue;
        }

        $processedComponents = [];
        $componentQueue = new SplQueue();
        $componentQueue->enqueue([$connectedComponent, [$connectedComponent]]);
        $pathFound = false;

        while (!$componentQueue->isEmpty() && !$pathFound && $connectionCount < MAX_CONNECTION_COUNT) {
            list($queuedComponent, $queuedPath) = $componentQueue->dequeue();

            foreach ($componentRelations[$queuedComponent] as $componentFromQueue) {
                if ($secondaryComponent == $componentFromQueue) {
                    $connectionCount++;
                    $visitedComponents = array_merge($visitedComponents, $queuedPath);
                    $pathFound = true;
                    break;
                } elseif (!in_array($componentFromQueue, $processedComponents) && !in_array($componentFromQueue, $queuedPath) && !in_array($componentFromQueue, $visitedComponents)) {
                    $componentQueue->enqueue([$componentFromQueue, array_merge($queuedPath, [$componentFromQueue])]);
                    $processedComponents[] = $componentFromQueue;
                }
            }
        }
    }

    return $connectionCount;
}

/**
 * Main function of the program.
 *
 * This function processes the data from the input file, determines the connected and unconnected group counts,
 * and returns the product of the connected and unconnected group counts.
 *
 * @return int The product of the connected and unconnected group counts.
 */
function multipleSizesOfGroups(): int
{
    $connectedGroupCount = 1;
    $unconnectedGroupCount = 0;

    // Renamed variables for improved readability
    $componentRelations = [];
    $dataFromFile = file(INPUT_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($dataFromFile as $line) {
        list($primaryComponent, $relatedComponents) = explode(': ', $line);
        $relatedComponents = explode(' ', $relatedComponents);
        foreach ($relatedComponents as $relatedComponent) {
            $componentRelations[$primaryComponent][] = $relatedComponent;
            $componentRelations[$relatedComponent][] = $primaryComponent;
        }
    }

    $startingComponent = array_key_first($componentRelations);

    foreach (array_slice(array_keys($componentRelations), 1) as $secondaryComponent) {
        $connectionCount = processConnectedComponents($componentRelations, $startingComponent, $secondaryComponent);

        if ($connectionCount >= MAX_CONNECTION_COUNT) {
            $connectedGroupCount++;
        } else {
            $unconnectedGroupCount++;
        }
    }

    return $connectedGroupCount * $unconnectedGroupCount;
}

$result = multipleSizesOfGroups();

echo "multiply the sizes of these two groups together: " . $result;

