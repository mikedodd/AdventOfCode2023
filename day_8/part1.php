<?php

/**
 * Parses the input file and returns an array containing the instructions and network graph.
 *
 * @param string $filePath The path to the input file.
 * @return array An associative array containing the parsed instructions and network graph.
 */
function parseInput(string $filePath): array {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES);
    $instructions = array_shift($lines); // Extract the first line as instructions
    $network = [];

    foreach ($lines as $line) {
        if($line) {
            list($node, $connections) = explode(' = ', $line);
            $network[$node] = explode(', ', trim($connections, '()'));
        }
    }

    return ['instructions' => $instructions, 'network' => $network];
}

/**
 * Navigates through a network graph based on a set of instructions.
 *
 * @param array $network The network graph represented as an associative array.
 * @param string $instructions The instructions on which direction to move at each step.
 * @return int The number of steps taken to reach the destination node.
 * @throws Exception If the current node is not found in the network.
 */
function navigateNetwork(array $network, string $instructions): int {
    $currentNode = 'AAA';
    $steps = 0;

    while ($currentNode != 'ZZZ') {
        if (!isset($network[$currentNode])) {
            // Handle missing node in the network
            throw new Exception("Node '{$currentNode}' not found in the network.");
        }

        $direction = $instructions[$steps % strlen($instructions)] === 'R' ? 1 : 0;
        $currentNode = $network[$currentNode][$direction];
        $steps++;
    }

    return $steps;
}

$filePath = 'input.txt';
$data = parseInput($filePath);

echo "Steps to reach ZZZ: " . navigateNetwork($data['network'], $data['instructions']) . "\n";
