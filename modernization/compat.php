<?php
/**
 * Compatibility helpers for the 2008 Mystery of Heroes codebase.
 *
 * This file is intentionally small and transitional: it lets the legacy
 * application run on modern PHP while the business logic is migrated away
 * from mysql_* and the removed PHP session/ereg APIs.
 */

if (!defined('MYSQL_ASSOC')) define('MYSQL_ASSOC', 1);
if (!defined('MYSQL_NUM')) define('MYSQL_NUM', 2);
if (!defined('MYSQL_BOTH')) define('MYSQL_BOTH', 3);

if (!function_exists('mysql_connect')) {
    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0) {
        $link = mysqli_connect($server, $username, $password);
        if ($link) {
            $GLOBALS['_legacy_mysql_link'] = $link;
        }
        return $link;
    }

    function mysql_select_db($database, $link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        return $link_identifier ? mysqli_select_db($link_identifier, $database) : false;
    }

    function mysql_query($query, $link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        return $link_identifier ? mysqli_query($link_identifier, $query) : false;
    }

    function mysql_fetch_array($result, $result_type = MYSQL_BOTH) {
        if (!$result instanceof mysqli_result) return false;
        $mode = $result_type === MYSQL_ASSOC ? MYSQLI_ASSOC : ($result_type === MYSQL_NUM ? MYSQLI_NUM : MYSQLI_BOTH);
        return mysqli_fetch_array($result, $mode);
    }

    function mysql_fetch_row($result) {
        return $result instanceof mysqli_result ? mysqli_fetch_row($result) : false;
    }

    function mysql_num_rows($result) {
        return $result instanceof mysqli_result ? mysqli_num_rows($result) : false;
    }

    function mysql_error($link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        return $link_identifier ? mysqli_error($link_identifier) : '';
    }

    function mysql_errno($link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        return $link_identifier ? mysqli_errno($link_identifier) : 0;
    }

    function mysql_insert_id($link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        return $link_identifier ? mysqli_insert_id($link_identifier) : 0;
    }

    function mysql_close($link_identifier = null) {
        $link_identifier = $link_identifier ?: ($GLOBALS['_legacy_mysql_link'] ?? null);
        if (!$link_identifier) return false;
        $result = mysqli_close($link_identifier);
        if ($link_identifier === ($GLOBALS['_legacy_mysql_link'] ?? null)) {
            unset($GLOBALS['_legacy_mysql_link']);
        }
        return $result;
    }
}

if (!function_exists('ereg')) {
    function ereg($pattern, $string, &$regs = null) {
        $pattern = '~' . str_replace('~', '\\~', $pattern) . '~';
        $matches = [];
        $result = preg_match($pattern, $string, $matches);
        if (is_array($regs)) $regs = $matches;
        return $result ? strlen($matches[0]) : false;
    }
}

if (!function_exists('eregi')) {
    function eregi($pattern, $string, &$regs = null) {
        $pattern = '~' . str_replace('~', '\\~', $pattern) . '~i';
        $matches = [];
        $result = preg_match($pattern, $string, $matches);
        if (is_array($regs)) $regs = $matches;
        return $result ? strlen($matches[0]) : false;
    }
}

if (!function_exists('session_register')) {
    function session_register(...$names) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        foreach ($names as $name) {
            $name = ltrim($name, '$');
            if (array_key_exists($name, $GLOBALS)) {
                $_SESSION[$name] = $GLOBALS[$name];
            }
        }
        return true;
    }

    function session_unregister($name) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $name = ltrim($name, '$');
        unset($_SESSION[$name]);
        return true;
    }

    function session_is_registered($name) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $name = ltrim($name, '$');
        return isset($_SESSION[$name]);
    }
}
