<?php
// Router.php
class Router {
    private $routes = [];

    public function addRoute($path, $callback)
    {
        $this->routes[$path] = $callback;
    }

    public function handleRequest($uri)
    {
        if (array_key_exists($uri, $this->routes)) {
            call_user_func($this->routes[$uri]);
        } else {
            // Handle 404
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
?>
