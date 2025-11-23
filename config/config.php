<?php
/**
 * Global configuration file for PeaceLink.
 * Update the database credentials to match your local environment.
 */

return [
    'db' => [
        'host' => 'localhost',
        'name' => 'peacelink',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        // Adjust when deploying behind Apache/Nginx virtual host
        'base_url' => '/peaceforum/public',
        'debug' => true,
    ],
];

