<?php

error_reporting(0);

# Exits with the provided error code if the condition is false
function mandate(int $code, bool $condition) {
    http_response_code($code);
    if(!$condition) die;
    http_response_code(200);
}

# Takes an array of associative arrays, each array representing
# a row and the keys representing the headers
function array_to_csv(array $assoc_array): string {
    $file = fopen('php://memory', 'r+');
    fputcsv($file, array_keys($assoc_array[0]));
    foreach($assoc_array as $row) fputcsv($file, array_values($row));
    rewind($file);
    return stream_get_contents($file);
}

# Calls the provided function until it returns false, returns an
# array with the resulting values
function collect(callable $callable): array {
    $values = [];
    while($value = $callable()) $values[] = $value;
    return $values;
}

$config = json_decode(file_get_contents('../private/config.json'), true);

mandate(500, $config !== null); # Internal Server Error
mandate(401, isset($_GET['key']) && $_GET['key'] === $config['key']); # Unauthorized

$database = new SQLite3('../private/database.sqlite3');

if($_GET['csv'] ?? false && $config['enable_csv_endpoint'] ?? false) {
    $query = $database->query('SELECT * FROM points ORDER BY timestamp');
    $rows = collect(fn() => $query->fetchArray(SQLITE3_ASSOC));
    $csv = array_to_csv($rows);
    header('content-type: text/plain');
    die($csv);
}

mandate(405, $_SERVER['REQUEST_METHOD'] === 'POST'); # Method Not Allowed

$data = json_decode(file_get_contents('php://input'), true);
mandate(400, $data !== null); # Bad Request

$database->exec(file_get_contents('../private/schema.sql'));
$statement = $database->prepare('INSERT INTO points VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

foreach($data['locations'] as $point) {

    # combine array of motion strings into a constant
    # eg: [walking, running, biking] -> biking-running-walking
    $motion_array = $point['properties']['motion'] ?? [];
    sort($motion_array);
    $motion_string = implode('-', $motion_array);

    foreach(array_values([
        'timestamp'           => strtotime($point['properties']['timestamp']),
        'latitude'            => $point['geometry']['coordinates'][1],
        'longitude'           => $point['geometry']['coordinates'][0],
        'altitude'            => $point['properties']['altitude'],
        'speed'               => $point['properties']['speed'],
        'motion'              => $motion_string,
        'horizontal_accuracy' => $point['properties']['horizontal_accuracy'],
        'vertical_accuracy'   => $point['properties']['vertical_accuracy'],
        'wifi'                => $point['properties']['wifi'] ?: null,
        'battery_state'       => $point['properties']['battery_state'],
        'battery_level'       => $point['properties']['battery_level']
    ]) as $index => $value) {
        $statement->bindValue($index + 1, $value);
    }

    $statement->execute();
    $statement->clear();

}

http_response_code(200);
header('content-type: application/json');
echo json_encode(['result' => 'ok']);