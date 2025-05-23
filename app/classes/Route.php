<?php

namespace app\classes;

class Route {
    private static $routes = [];
    private static $prefix = '';
    private static $middleware = [];

    public static function get($uri, $controller) {
        self::addRoute('GET', $uri, $controller);
    }

    public static function post($uri, $controller) {
        self::addRoute('POST', $uri, $controller);
    }

    public static function group($prefix, $callback) {
        $previousPrefix = self::$prefix;
        self::$prefix .= $prefix;
        
        $callback();
        
        self::$prefix = $previousPrefix;
    }

    public static function middleware($middleware, $callback) {
        $previousMiddleware = self::$middleware;
        self::$middleware[] = $middleware;
        
        $callback();
        
        self::$middleware = $previousMiddleware;
    }

    private static function addRoute($method, $uri, $controller) {
        $uri = trim(self::$prefix . '/' . trim($uri, '/'), '/');
        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'middleware' => self::$middleware
        ];
    }

    public static function getRoutes() {
        return self::$routes;
    }
}
