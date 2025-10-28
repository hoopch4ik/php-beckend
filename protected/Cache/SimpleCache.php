<?php
namespace Protected\Cache;


class SimpleCache
{
    protected string $cacheDir;
    protected int $ttl; // Time-to-live в секундах

    public function __construct(string $cacheDir = null, int $ttl = 10)
    {
        $this->cacheDir = $cacheDir ?? ('cache'); // Папка для хранения кэша
        $this->ttl = $ttl; // Время жизни кэша в секундах (по умолчанию 1 час)

        $this->ensureCacheDir();
        $this->setCorsHeaders();
    }

    // Создаёт папку для хранения кэша, если она отсутствует
    protected function ensureCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!@mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
                error_log("Cannot create cache dir: {$this->cacheDir}");
            }
        }
    }

    // Формирует уникальный ключ для кэша на основе URL запроса
    protected function getCacheKey(): string
    {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
        return md5($url);
    }

    // Получает путь к файлу кэша для данного запроса
    protected function getCachePath(): string
    {
        $key = $this->getCacheKey();
        return $this->cacheDir . '/' . $key . '.cache';
    }

    // Проверяет, есть ли кэш и не истек ли его срок действия
    protected function isCacheValid(): bool
    {
        $cachePath = $this->getCachePath();
        if (!file_exists($cachePath)) {
            return false; // Кэша нет
        }

        $mtime = filemtime($cachePath); // Время последнего изменения файла
        return (time() - $mtime) <= $this->ttl; // Проверяем TTL
    }

    // // Получает данные из кэша
    // protected function getCachedData(): string
    // {
    //     $cachePath = $this->getCachePath();
    //     return file_get_contents($cachePath);
    // }

    // Сохраняет данные в кэш
    protected function updateCache(): void
    {
        $cachePath = $this->getCachePath();
        @file_put_contents($cachePath, "1", LOCK_EX);
    }

    // Запускает обработку кэша
    public function handleRequest()
    {
        if (strtolower($_SERVER['REQUEST_METHOD']) != "get") return;
        if ($this->isCacheValid()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
            exit;
        }

        $this->updateCache();
    }

    // Устанавливает заголовки CORS
    protected function setCorsHeaders(): void
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
        // Устанавливаем Access-Control-Allow-Headers только если клиент прислал Access-Control-Request-Headers
        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            // безопасно используем значение, без прямой интерполяции строки
            header('Access-Control-Allow-Headers: ' . $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']);
        } else {
            // либо явный список
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
    }
}