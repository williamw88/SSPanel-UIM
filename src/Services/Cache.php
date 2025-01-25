<?php

declare(strict_types=1);

namespace App\Services;

use Redis;
use Exception;

final class Cache
{
    public function initRedis(): Redis
    {
        $config = self::getRedisConfig();
        $redis = new Redis();

        try {
            // Connect to Redis server
            $redis->connect($config['host'], $config['port'], $config['connectTimeout']);

            // Authenticate if credentials are provided
            if (isset($config['auth']['user']) && isset($config['auth']['pass'])) {
                $redis->auth([$config['auth']['user'], $config['auth']['pass']]);
            } elseif (isset($config['auth']['pass'])) {
                $redis->auth($config['auth']['pass']);
            }

            // Enable SSL if required
            if (isset($config['ssl']) && $config['ssl'] === true) {
                $redis->setOption(Redis::OPT_SSL_CONTEXT, $config['ssl_context']);
            }

            // Set read timeout
            $redis->setOption(Redis::OPT_READ_TIMEOUT, $config['readTimeout']);
        } catch (Exception $e) {
            // Log error and rethrow for debugging
            error_log("Redis connection failed: " . $e->getMessage());
            throw $e;
        }

        return $redis;
    }

    public static function getRedisConfig(): array
    {
        return [
            'host' => $_ENV['redis_host'] ?? '127.0.0.1',
            'port' => (int)($_ENV['redis_port'] ?? 6379),
            'connectTimeout' => (float)($_ENV['redis_connect_timeout'] ?? 1.5),
            'readTimeout' => (float)($_ENV['redis_read_timeout'] ?? 1.5),
            'auth' => [
                'user' => $_ENV['redis_username'] ?? '',
                'pass' => $_ENV['redis_password'] ?? '',
            ],
            'ssl' => filter_var($_ENV['redis_ssl'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'ssl_context' => $_ENV['redis_ssl_context'] ?? null,
        ];
    }
}
