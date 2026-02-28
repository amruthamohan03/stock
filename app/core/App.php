<?php
class App {
    protected $controller = 'AuthController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url            = $this->parseUrl();
        $urlPath        = implode('/', $url);
        $controllerPath = '';

        // 🔹 Custom route definitions
        $routes = [
            'dashboard'        => ['MenuController', 'dashboard'],
            'menu'             => ['MenuController', 'menu'],
            'menu_management'  => ['MenuController', 'menu_management'],
            'login'            => ['AuthController', 'login'],
            'logout'           => ['AuthController', 'logout'],
        ];

        // 🔹 Controller path mapping
        $pathRouting = [
            'menu'                  => 'masters/',
            'make'                  => 'masters/',
            'model'                 => 'masters/',
            'role'                  => 'masters/',
            'user'                  => 'masters/',
            'department'            => 'masters/',
            'provider'              => 'masters/',
            'indent'                => 'stock/',
            'stock'                 => 'stock/',
            'daybook'               => 'stock/',
            'book'                  => 'report/',
            'item'                  => 'masters/',
            'account'               => 'account/',
            'rolemenumapping'       => 'mapping/',
            'clientbankmapping'     => 'mapping/',
            'roleDashboardCard'     => 'mapping/',
            'dashboardCard'         => 'masters/',
            'live'                  => 'report/',
            'custom'                => 'report/',

        ];

        // 🔹 1️⃣ Check custom routes first
        if (isset($routes[$urlPath])) {
            [$this->controller, $this->method] = $routes[$urlPath];
        } 
        else {            
            // 🔹 2️⃣ Default MVC routing
            if (!empty($url[0])) {
                $controllerPath = (!empty($url[0]) && !empty($pathRouting[$url[0]])) ? $pathRouting[$url[0]] : '';
                $controllerName = ucfirst($url[0]) . 'Controller';
                if (file_exists('../app/controllers/' .$controllerPath. $controllerName . '.php')) {
                    $this->controller = $controllerName;
                    unset($url[0]);
                }
            }
            
            require_once '../app/controllers/'.$controllerPath . $this->controller . '.php';
            $this->controller = new $this->controller;

            if (isset($url[1])) {
                $methodName = str_replace('-', '_', $url[1]);
                if (method_exists($this->controller, $methodName)) {
                    $this->method = $methodName;
                    unset($url[1]);
                }
            }

            $this->params = $url ? array_values($url) : [];
            call_user_func_array([$this->controller, $this->method], $this->params);
            return;
        }

        // 🔹 3️⃣ Handle custom route
        if (!file_exists('../app/controllers/' . $this->controller . '.php')) {
            die("❌ Controller not found: {$this->controller}");
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (!method_exists($this->controller, $this->method)) {
            die("❌ Method '{$this->method}' not found in controller '{$this->controller}'");
        }

        call_user_func_array([$this->controller, $this->method], []);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
