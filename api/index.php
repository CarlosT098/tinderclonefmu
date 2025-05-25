<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/Core/OutputHandler.php';
start_output_handler(); 

header('Content-Type: application/json');
require __DIR__ . '/../vendor/autoload.php';


//REMOVER ISSO AO SUBIR PARA PROD
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}


spl_autoload_register(function($class) {
    $path = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($path)) {
        require_once $path;
    }
});
require_once __DIR__ . '/../src/Core/App.php';
require_once __DIR__ . '/../src/Core/Router.php';

$router = new Router();

require_once __DIR__ . '/../src/Routes/web.php';

$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

//error_log("Dispatching for URI: $uri with method: $method");
//error_log("Requested URI: " . $_SERVER['REQUEST_URI']);
//error_log("Iniciando o index.php");
try {
    $router->dispatch($uri, $method);
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}


end_output_handler();