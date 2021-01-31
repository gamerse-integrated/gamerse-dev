<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
include_once '../config/Database.php';
include_once '../models/Player.php';

// instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// initialize object
$player = new Player($db);

$requestMethod = $_SERVER["REQUEST_METHOD"];
// $uri = parse_url($_SERVER['REQUEST_URI']);
// $uri = $_SERVER['REQUEST_URI'];
$query_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($query_str, $uri);
// print_r($uri);
// $uri = explode('/', $uri);
$userName = null;
if (isset($uri['userName'])) {
    $userName = (int) $uri['userName'];
}
// print_r($uri);
// echo("userName: ".$userName);


switch ($requestMethod) {
    case 'GET':
        if ($userName) {
            $response = getUser($player,$userName);
        } else {
            $response = getAllUsers($player);
        }
        ;
        break;
    case 'POST':
        $response = createUser($player);
        break;
    case 'PUT':
        $response = updateUser($player,$userName);
        break;
    case 'DELETE':
        $response = deleteUser($player,$userName);
        break;
    default:
        $response = notFoundResponse();
        break;
}

header($response['status_code_header']);
if ($response['body']) {
    echo $response['body'];
}




function getAllUsers($player)
{
    $result = $player->findAll();
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function getUser($player,$userName)
{
    $result = $player->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    
    return $response;
}

function createUser($player)
{

    $input = (array) json_decode(file_get_contents('php://input'), true);
    // print_r($input);
    if (!valuserNameatePerson($input)) {
        return unprocessableEntityResponse();
    }
    $player->insert($input);
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = null;
    return $response;
}

function updateUser($player,$userName)
{
    $result = $player->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $input = (array) json_decode(file_get_contents('php://input'), true);
    if (!valuserNameatePerson($input)) {
        return unprocessableEntityResponse();
    }
    $player->update($userName, $input);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = null;
    return $response;
}

function deleteUser($player,$userName)
{
    $result = $player->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $player->delete($userName);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = null;
    return $response;
}

function valuserNameatePerson($input)
{
    if (!isset($input['userName'])) {
        return false;
    }

    return true;
}

function unprocessableEntityResponse()
{
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
        'error' => 'Invalid input',
    ]);
    return $response;
}

function notFoundResponse()
{
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = null;
    return $response;
}