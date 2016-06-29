<?php

namespace core\components\router;

class Router
{
  private static $uri;
  private static $routes;

  public static function match($uri = null, $rootDir = null, $routes = null)
  {
    self::$uri = substr($uri, strlen($rootDir));
    self::$routes = $routes;

    if (is_string(self::$uri) && is_array(self::$routes))
      return self::matcher();
    else
      return false;
  }

  private static function matcher()
  {
    $response = [
      'path' => '',
      'action' => '',
      'data' => [],
      'error' => '',
    ];

    foreach (self::$routes as $route => $action) {
      $routeMatcher = self::regexBuilder($route);

      if(preg_match($routeMatcher['regex'], self::$uri, $extract)) {
        $response['path'] = self::$uri;
        $response['action'] = $action;
        array_shift($extract);

        if (!empty($routeMatcher['params'])) {
          foreach ($routeMatcher['params'] as $key => $value) {
            $response['data'][ltrim($value, ":")] = $extract[$key];
          }
        }

        return $response;
      }
    }

    $response['error'] = "404 not found";
    return $response;
  }

  private static function regexBuilder($route)
  {
    $params = explode("/", $route);
    array_shift($params);

    $var = [];
    $regex = "/^";

    if (empty($params[0])) {
      $regex .= "\/";
    } else {
        foreach ($params as $key => $value) {
        $regex .= "\/";

        if ($value[0] === ":") {
          $regex .= "([A-Za-z0-9-]+)";
          $var[] = $value;
        } else {
          $regex .= $value;
        }
      }
    }

    return [
      'regex' => $regex . "$/",
      'params' => $var,
    ];
  }
}
