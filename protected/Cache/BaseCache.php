<?php
namespace Protected\Cache;

class BaseCache
{
    protected string $cacheDir;
    protected string $resourceKey; // хеш ресурса (host + path)
    protected string $mtimePath;   // путь к файлу с меткой времени

    public function __construct(string $cacheDir = null)
    {
        // По умолчанию папка cache рядом с этим файлом
        $this->cacheDir = $cacheDir ?? ('cache');

        $this->ensureCacheDir();

        $this->resourceKey = $this->makeResourceKey();
        $this->mtimePath = $this->cacheDir . '/' . $this->resourceKey . '.baseCache';

        $this->setCorsHeaders();

        // Если preflight OPTIONS — сразу отдаем 204 (без тела)
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if ($method !== 'GET') {
            // инвалидация/обновление времени на записьных методах
            $this->touchModified();
            // Не останавливаем выполнение — пусть основной обработчик отдаст ответ
            return;
        }

        // Обработка GET: проверяем If-None-Match и If-Modified-Since
        $lastModified = $this->getModified(); // int (timestamp)
        $etag = $this->makeEtag($lastModified);

        header('ETag: ' . $etag);
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');

        $ifNoneMatch = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;
        if ($ifNoneMatch && $this->clientHasMatchingEtag($ifNoneMatch, $etag)) {
            // Клиент имеет ту же версию — 304
            header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
            exit;
        }

        $ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? null;
        if ($ifModifiedSince) {
            $clientTime = strtotime(explode(';', $ifModifiedSince)[0]); // отбросить возможные ;gzip-data
            if ($clientTime && $lastModified <= $clientTime) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified', true, 304);
                exit;
            }
        }
    }

    // Создаёт каталог cache, если не существует
    protected function ensureCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            if (!@mkdir($this->cacheDir, 0775, true) && !is_dir($this->cacheDir)) {
                // Невозможно создать кеш-папку — логируем и продолжаем (без фатальной ошибки)
                error_log("Cannot create cache dir: {$this->cacheDir}");
            }
        }
    }

    // Формирует ключ ресурса на основе host + path + normalized query
    // protected function makeResourceKey(): string
    // {
    //     $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'unknown');
    //     $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    //     // Нормализуем query: возьмем query строку и отсортируем параметры (чтобы одинаковые запросы с другим порядком были эквивалентны)
    //     $query = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?? '';
    //     if ($query !== '') {
    //         parse_str($query, $qArr);
    //         ksort($qArr);
    //         $normalizedQuery = http_build_query($qArr);
    //     } else {
    //         $normalizedQuery = '';
    //     }

    //     $raw = $host . '|' . $uriPath . ($normalizedQuery ? '?' . $normalizedQuery : '');
    //     return md5($raw);
    // }
    protected function makeResourceKey(): string
    {
        $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'unknown');
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $raw = $host.$uriPath;
        return md5($raw);
    }

    // Возвращает Unix timestamp последнего изменения ресурса или создаёт его (текущее время) если ещё нет
    protected function getModified(): int
    {
        if (is_file($this->mtimePath)) {
            $contents = @file_get_contents($this->mtimePath);
            // fallback: filemtime
            return (int)@filemtime($this->mtimePath);
        }
        // Если файла нет — создаём с текущим временем
        $now = time();
        $this->writeModified($now);
        return $now;
    }

    // Обновляет метку на текущее время и возвращает новое время
    protected function touchModified(): int
    {
        $now = time();
        $this->writeModified($now);
        return $now;
    }

    // Записывает timestamp в файл с LOCK_EX
    protected function writeModified(int $timestamp): void
    {
        @file_put_contents($this->mtimePath, "", LOCK_EX);
        @touch($this->mtimePath, $timestamp);
    }

    // Генерация ETag: используем ресурсный ключ и lastModified
    protected function makeEtag(int $lastModified): string
    {
        // ETag в кавычках — это рекомендованный формат
        return '"' . md5($this->resourceKey . ':' . $lastModified) . '"';
    }

    // Сравниваем If-None-Match (возможны несколько ETag разделённых запятыми) с текущим ETag
    protected function clientHasMatchingEtag(string $clientHeader, string $currentEtag): bool
    {
        // clientHeader может быть вида: W/"abc", "xyz"
        $parts = array_map('trim', explode(',', $clientHeader));
        foreach ($parts as $p) {
            // удаляем префикс W/ и кавычки для нормализации
            $norm = preg_replace('/^\s*W\/?/', '', $p);
            $norm = trim($norm, " \t\n\r\0\x0B\""); // убираем кавычки и пробелы
            $cur = trim($currentEtag, "\"");
            if ($norm === $cur) {
                return true;
            }
        }
        return false;
    }

    // Устанавливаем CORS и заголовки безопасно
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

    // Алиас для совместимости с оригинальным названием метода (если нужно)
    public static function cacheExists(string $cacheDir, string $resourceKey): bool
    {
        $path = rtrim($cacheDir, '/\\') . '/' . $resourceKey . '.mtime';
        return is_file($path);
    }
}
