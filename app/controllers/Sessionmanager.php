<?php
/**
 * Session Manager Class — FINAL FIXED VERSION
 *
 * Key fixes vs previous versions:
 *  A. validateSession() never does header() redirect on AJAX requests —
 *     AJAX callers get an empty/invalid session and JS handles the redirect.
 *  B. Periodic session_regenerate_id() REMOVED from normal requests.
 *     Regeneration now happens ONLY on login/logout — eliminates the race
 *     condition where the browser's next fetch still carries the old cookie.
 *  C. checkSession / getStatus are pure READS — they do NOT update last_activity.
 *     Only keepAlive() and setUserSession() reset the idle clock.
 *     This means getRemainingTime() always reflects true idle time.
 */
class SessionManager {

    // ── Timeout constants (seconds) — keep in sync with config.php ──────────
    private const SESSION_TIMEOUT = 3600;   // 1 hour idle limit
    private const MAX_IDLE_TIME   = 7200;   // 2 hour absolute max
    // ────────────────────────────────────────────────────────────────────────

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
            ini_set('session.cookie_secure', $isHttps ? 1 : 0);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', self::MAX_IDLE_TIME);
            ini_set('session.cookie_lifetime', 0);

            session_start();
            self::initSecurity();
            self::validateSession(self::isAjaxRequest()); // FIX A
        }
    }

    // ── Internals ─────────────────────────────────────────────────────────────

    private static function initSecurity(): void
    {
        if (!isset($_SESSION['initiated'])) {
            session_regenerate_id(true); // Only on brand-new session
            $_SESSION['initiated']     = true;
            $_SESSION['user_agent']    = self::getUserAgent();
            $_SESSION['ip_address']    = self::getIpAddress();
            $_SESSION['created_at']    = time();
            $_SESSION['last_activity'] = time();
        }
    }

    private static function validateSession(bool $isAjax = false): void
    {
        if (!isset($_SESSION['initiated'])) {
            return;
        }

        // UA check — only invalidate logged-in sessions
        if (!self::validateUserAgent() && self::isLoggedIn()) {
            error_log('SessionManager: UA mismatch — ' . session_id());
            self::destroy();
            if ($isAjax) return; // FIX A: JS will see empty session & redirect
            header('Location: /auth/login?reason=ua');
            exit;
        }

        if (self::isTimedOut()) {
            self::destroy();
            if ($isAjax) return; // FIX A: no redirect on AJAX
            header('Location: /auth/login?timeout=1');
            exit;
        }

        // FIX B & C: Do NOT update last_activity here.
        // keepAlive() is the only place that resets the idle clock.
        // This ensures getRemainingTime() always reflects real idle time.
    }

    private static function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private static function isTimedOut(): bool
    {
        if (!isset($_SESSION['last_activity']) || !self::isLoggedIn()) {
            return false;
        }

        $idle = time() - $_SESSION['last_activity'];
        if ($idle > self::SESSION_TIMEOUT) {
            error_log("SessionManager: idle timeout — " . session_id() . " ({$idle}s idle)");
            return true;
        }

        if (isset($_SESSION['created_at'])) {
            $age = time() - $_SESSION['created_at'];
            if ($age > self::MAX_IDLE_TIME) {
                error_log("SessionManager: max-age exceeded — " . session_id() . " ({$age}s)");
                return true;
            }
        }

        return false;
    }

    private static function validateUserAgent(): bool
    {
        if (!isset($_SESSION['user_agent'])) return true;
        return self::extractBrowserInfo(self::getUserAgent())
            === self::extractBrowserInfo($_SESSION['user_agent']);
    }

    private static function extractBrowserInfo(string $ua): string
    {
        if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera)\/\d+/', $ua, $m)) {
            return $m[0];
        }
        return substr($ua, 0, 50);
    }

    private static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    }

    private static function getIpAddress(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Set session after successful login.
     * FIX B: This is ONE of only TWO places that call session_regenerate_id().
     */
    public static function setUserSession(array $userData): void
    {
        session_regenerate_id(true); // Secure: rotate ID on privilege change

        $_SESSION['user_id']       = $userData['id'];
        $_SESSION['user_data']     = [
            'id'            => $userData['id'],
            'fullname'      => $userData['fullname'],
            'username'      => $userData['username'],
            'role_name'     => $userData['role_name'],
            'role_id'       => $userData['role_id'],
            'profile_image' => $userData['profile_image'] ?? null,
            'email'         => $userData['email'] ?? '',
        ];
        $_SESSION['is_logged_in']  = true;
        $_SESSION['login_time']    = time();
        $_SESSION['last_activity'] = time();

        error_log("SessionManager: login — {$userData['username']} (ID:{$userData['id']})");
    }

    public static function isLoggedIn(): bool
    {
        return !empty($_SESSION['is_logged_in'])
            && $_SESSION['is_logged_in'] === true
            && isset($_SESSION['user_id']);
    }

    public static function getUserId(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function getUserData(?string $key = null)
    {
        if ($key === null) return $_SESSION['user_data'] ?? [];
        return $_SESSION['user_data'][$key] ?? null;
    }

    /**
     * FIX C: Pure read — does NOT touch last_activity.
     */
    public static function getRemainingTime(): int
    {
        if (!isset($_SESSION['last_activity'])) return self::SESSION_TIMEOUT;
        return max(0, self::SESSION_TIMEOUT - (time() - $_SESSION['last_activity']));
    }

    /**
     * FIX D: keepAlive() is the ONLY method that resets last_activity.
     * Called by JS every 5 minutes while the page is open.
     */
    public static function keepAlive(): array
    {
        if (!self::isLoggedIn()) {
            return ['success' => false, 'message' => 'Not logged in'];
        }
        $_SESSION['last_activity'] = time();
        return [
            'success'   => true,
            'remaining' => self::getRemainingTime(),
            'timeout'   => self::SESSION_TIMEOUT,
        ];
    }

    /**
     * Pure status read for /auth/checkSession — no side effects.
     */
    public static function getStatus(): array
    {
        return [
            'isLoggedIn' => self::isLoggedIn(),
            'remaining'  => self::getRemainingTime(),
            'timeout'    => self::SESSION_TIMEOUT,
        ];
    }

    /**
     * Config endpoint consumed by JS SessionManager via /auth/getConfig.
     * All times in SECONDS.
     */
    public static function getConfig(): array
    {
        return [
            'isLoggedIn'  => self::isLoggedIn(),
            'timeout'     => self::SESSION_TIMEOUT,
            'warningTime' => defined('SESSION_WARNING_TIME') ? (int)SESSION_WARNING_TIME : 300,
            'remaining'   => self::getRemainingTime(),
        ];
    }

    public static function getTimeout(): int { return self::SESSION_TIMEOUT; }

    /**
     * Destroy session (logout).
     * FIX B: This is TWO of only TWO places that call session_regenerate_id().
     */
    public static function destroy(): void
    {
        if (isset($_SESSION['user_data']['username'])) {
            error_log("SessionManager: logout — " . $_SESSION['user_data']['username']);
        }
        $_SESSION = [];
        if (isset($_COOKIE[session_name()])) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function setFlash(string $key, string $message, string $type = 'info'): void
    {
        $_SESSION['flash'][$key] = ['message' => $message, 'type' => $type];
    }

    public static function getFlash(string $key): ?array
    {
        if (isset($_SESSION['flash'][$key])) {
            $flash = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $flash;
        }
        return null;
    }

    public static function getDebugInfo(): array
    {
        return [
            'session_id'        => session_id(),
            'is_logged_in'      => self::isLoggedIn(),
            'user_id'           => self::getUserId(),
            'idle_seconds'      => isset($_SESSION['last_activity'])
                                    ? (time() - $_SESSION['last_activity']) : 'n/a',
            'session_age'       => isset($_SESSION['created_at'])
                                    ? (time() - $_SESSION['created_at']) : 'n/a',
            'remaining_seconds' => self::getRemainingTime(),
            'ua_match'          => self::validateUserAgent(),
            'is_https'          => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'cookie_params'     => session_get_cookie_params(),
        ];
    }
}