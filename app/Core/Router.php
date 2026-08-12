<?php
/**
 * VICOBA Router — Clean URL routing
 * Replaces WordPress rewrite rules and VICOBA_Router
 */

class Router
{
    private static array $routes = [];

    public static function get(string $path, array|callable $handler): void
    {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, array|callable $handler): void
    {
        self::$routes['POST'][$path] = $handler;
    }

    public static function any(string $path, array|callable $handler): void
    {
        self::$routes['GET'][$path]  = $handler;
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');
        if ($uri === '//') $uri = '/';

        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            // Convert {param} to named capture groups
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $pattern);
            $regex = '@^' . $regex . '$@';

            if (preg_match($regex, $uri, $matches)) {
                // Extract named params only
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                self::call($handler, $params);
                return;
            }
        }

        // 404 fallback
        http_response_code(404);
        if (self::isApi($uri)) {
            Response::json(['success' => false, 'message' => 'Endpoint haikupatikana.'], 404);
        } else {
            include VIEW_PATH . '/errors/404.php';
        }
    }

    private static function call(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        [$class, $method] = $handler;
        $controller = new $class();
        $controller->$method($params);
    }

    private static function isApi(string $uri): bool
    {
        return str_starts_with($uri, '/api/');
    }

    /** Generate a URL for a given path */
    public static function url(string $path = '/', array $params = []): string
    {
        $base = rtrim(config('app.url'), '/');
        $path = '/' . ltrim($path, '/');
        $url  = $base . $path;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }
}
