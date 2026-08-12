<?php
/**
 * VICOBA Response — HTTP responses & redirects
 * Replaces wp_redirect, wp_send_json, wp_die
 */

class Response
{
    /** Send a JSON response and exit */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /** Redirect to a URL and exit */
    public static function redirect(string $path, int $code = 302): never
    {
        $url = str_starts_with($path, 'http') ? $path : Router::url($path);
        header('Location: ' . $url, true, $code);
        exit;
    }

    /** Render a view file with given data */
    public static function view(string $view, array $data = []): void
    {
        // Make data variables available in view
        extract($data, EXTR_SKIP);

        $file = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException("View not found: $view ($file)");
        }
        include $file;
    }

    /** Render a dashboard view inside the layout */
    public static function dashboard(string $view, array $data = []): void
    {
        $data['current_view'] = $view;
        $data['page_title']   = $data['page_title'] ?? 'Dashboard — VICOBA Manager';
        $data['current_user'] = $data['current_user'] ?? Auth::user();

        extract($data, EXTR_SKIP);

        include VIEW_PATH . '/dashboard/layout.php';
    }

    /** Send a file download (CSV, PDF) */
    public static function download(string $filename, string $content, string $mime = 'text/csv'): never
    {
        // Clear all output buffers
        while (ob_get_level()) @ob_end_clean();

        header('Content-Type: ' . $mime . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . strlen($content));
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

        echo $content;
        exit;
    }

    /** Output CSV content directly (for large files, avoids buffering) */
    public static function csv(string $filename, callable $writer): never
    {
        while (ob_get_level()) @ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel
        fputs($out, "\xEF\xBB\xBF");
        $writer($out);
        fclose($out);
        exit;
    }

    /** Abort with an error page */
    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        $messages = [403 => 'Huna Ruhusa', 404 => 'Ukurasa Haupo', 500 => 'Hitilafu ya Seva'];
        $title    = $messages[$code] ?? 'Hitilafu';
        echo "<!DOCTYPE html><html><head><title>$code — $title</title></head><body><h1>$code — $title</h1><p>" . htmlspecialchars($message) . "</p></body></html>";
        exit;
    }
}
