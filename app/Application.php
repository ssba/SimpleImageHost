<?php

namespace App;

class Application
{

    private $request = null;
    private $requestData = null;
    private $controller = null;
    private $method = null;

    private $guidREGEXPattern = "([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})";
    private $idREGEXPattern = "(.{32})";

    private $routes = [
        'get' => [
            "clear/8f3c1c7258564e3eafc6ee085147bac4" => "App\ImageController@clearObsolete",
            "/image/{id}" => "App\ImageController@getImage",
            "/" => "App\IndexController@index",
        ],
        'post' => [
            "/upload" => "App\ImageController@upload"
        ]
    ];

    private $http_vars = [];

    public function __construct()
    {
        $this->request = parse_url($_SERVER['REQUEST_URI']);

        parse_str(file_get_contents("php://input"), $POSTDATA);
        $this->requestData = array_merge($_GET, $_POST, $POSTDATA);

        $method = strtolower($_SERVER['REQUEST_METHOD']);
        foreach ($this->routes[$method] as $route => $controller) {

            $trimedRoute = trim($route, "//");

            $pattern = "/^\/*" . str_replace(['/', '{guid}', '{id}'], ['\/', $this->guidREGEXPattern, $this->idREGEXPattern], $trimedRoute) . "\/*/";

            if (preg_match($pattern, $this->request['path'], $matches, PREG_OFFSET_CAPTURE)) {

                array_shift($matches);
                foreach ($matches as $match) {
                    $match = current($match);
                    if (preg_match("/" . $this->guidREGEXPattern . "|" . $this->idREGEXPattern . "/", $match)) {
                        $this->http_vars[] = $match;
                    }
                }

                $controller = explode("@", $controller);

                $this->controller = $controller[0];
                $this->method = $controller[1];

                break;
            }
        }

        if (empty($this->controller))
            throw new \Exception();
        // TODO Exception wrapper

    }

    public function run()
    {
        $controller = new $this->controller;

        $this->http_vars['request_var'] = $this->requestData;
        $result = call_user_func_array(array($controller, $this->method), $this->http_vars);
        echo $result;

    }
}