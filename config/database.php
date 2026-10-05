<?php

declare(strict_types=1);

$environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

if (!is_readable($environmentFile)) {
    throw new RuntimeException('The .env file is missing or is not readable.');
}

$environment = parse_ini_file($environmentFile, false, INI_SCANNER_RAW);

if ($environment === false) {
    throw new RuntimeException('The .env file could not be parsed.');
}

$getEnvironmentValue = static function (string $key) use ($environment): string {
    $systemValue = getenv($key);

    if ($systemValue !== false) {
        return trim($systemValue);
    }

    $fileValue = $environment[$key] ?? '';

    return is_string($fileValue) ? trim($fileValue) : '';
};

$host = $getEnvironmentValue('DB_HOST');
$port = $getEnvironmentValue('DB_PORT');
$databaseName = $getEnvironmentValue('DB_NAME');
$username = $getEnvironmentValue('DB_USER');
$password = $getEnvironmentValue('DB_PASSWORD');

$requiredSettings = [
    'DB_HOST' => $host,
    'DB_PORT' => $port,
    'DB_NAME' => $databaseName,
    'DB_USER' => $username,
];

foreach ($requiredSettings as $setting => $value) {
    if ($value === '') {
        throw new RuntimeException(sprintf('%s must be set in the .env file.', $setting));
    }
}

if (filter_var($port, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === false) {
    throw new RuntimeException('DB_PORT must be a valid port number.');
}

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $host,
    $port,
    $databaseName
);

try {
    return new PDO(
        $dsn,
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $exception) {
    throw new RuntimeException(
        'Database connection failed. Check the database settings in the .env file.',
        0,
        $exception
    );
}
