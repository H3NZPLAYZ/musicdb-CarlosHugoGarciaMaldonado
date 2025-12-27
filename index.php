<?php
    require_once  "./vendor/autoload.php";
    require_once  "./autoload.php";
    use core\Session;
    use core\Request;
    use core\Auth;

    $model = $_GET["model"]??"home" ;
    $method = $_GET["method"]??"home" ;

    if (Session::active())
    {
        Session::update();
        if (($model=="home") and ($method=="home"))
        {
            Request::redirectToRoute("home") ;
        }
    }

    $controllerName = ucfirst("{$model}Controller") ;

    $className = "controllers\\{$controllerName}" ;

    $controller = new $className ;

    $controller->$method() ;


?>