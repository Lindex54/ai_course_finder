<?php

declare(strict_types=1);

// Reads the Gemini settings from the .env file. Both model settings are optional.
// The fallback model is used only when the main model is busy or unreachable.
$environmentFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

$environment = is_readable($environmentFile)
    ? parse_ini_file($environmentFile, false, INI_SCANNER_RAW)
    : false;

$environment = is_array($environment) ? $environment : [];

$primary = trim((string) ($environment['GEMINI_MODEL'] ?? '')) ?: 'gemini-3.8-flash';
$fallback = trim((string) ($environment['GEMINI_FALLBACK_MODEL'] ?? '')) ?: 'gemini-3.1-flash-lite';

return [
    'api_key' => trim((string) ($environment['GEMINI_API_KEY'] ?? '')),
    'models' => array_values(array_unique([$primary, $fallback])),
];
