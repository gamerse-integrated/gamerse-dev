<?php
 
 
 // header("Access-Control-Allow-Origin: *");
 // header("Content-Type: application/json; charset=UTF-8");
 // header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
 include_once '../config/Database.php';
 include_once '../models/Player.php';
 
 // Allow from any origin
 function cors() {
   if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, HEAD etc
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: Origin, Authorization, X-Requested-With, Content-Type, Accept");

    exit(0);
}
}
cors();




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
    $userName = $uri['userName'];
}

$email = null;
$input = (array) json_decode(file_get_contents('php://input'), true);
if (isset($input['email'])){
    $email = $input['email'];
}

// echo $email;

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
        // $response = postHey($player);
        if($email != null){
            // $response = $email;
            // $response['status_code_header'] = 'HTTP/1.1 200 OK';
            // $response = $uri;
            $response = setOnlineStatus($player,$email);
        }
        else{
            // $response['status_code_header'] = 'HTTP/1.1 200 OK';
            // $response['body'] = "email null";
            // $response['body'] = json_encode($uri);
            $response = createUser($player);
        };
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


function setOnlineStatus($player,$email){
    $result = $player->setOnlineStatus($email);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
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
    // echo $userName;
    $result = $player->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    // $result = 'hi';
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    
    return $response;
}

function postHey($player)
{

    $input = json_decode(file_get_contents('php://input'), true);
    // print_r($input);
    // if (!validatePerson($input)) {
    //     return unprocessableEntityResponse();
    // }
    // $player->insert($input);
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = null;
    return $response;
}

function createUser($player)
{

    $input = (array) json_decode(file_get_contents('php://input'), true);
    // print_r($input);
    if (!validatePerson($input)) {
        return unprocessableEntityResponse();
    }
    print_r($input);

    //check if user exists
    // if exists , then return error
    // else create player
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
    if (!validatePerson($input)) {
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

function validatePerson($input)
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