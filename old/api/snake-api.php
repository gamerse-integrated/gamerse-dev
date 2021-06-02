<?php


// header("Access-Control-Allow-Origin: *");
// header("Content-Type: application/json; charset=UTF-8");
// header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
include_once '../config/Database.php';
include_once '../models/Snake.php';

// Allow from any origin
function cors()
{
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
$player = new Snake($db);

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
$input = (array)json_decode(file_get_contents('php://input'), true);
if (isset($input['userName'])) {
    $userName = $input['userName'];
}

if (isset($input['score'])) {
    $score = $input['score'];
}




switch ($requestMethod) {
    case 'GET':
        if ($userName) {
            $response = getHighScore($player, $userName);
        } else {
            $response = notFoundResponse();
        }
        break;
    case 'POST':
        $a   = $player->getHighScore($userName);
        if ($score > $a["highscore"] && isset($userName) && isset($score)) {
            $response = setHighScore($player, $userName, $score);
        } else if (isset($userName) && isset($score)) {

            $response = setLastGame($player,   $userName, $score);
        }
        break;
    default:
        $response = notFoundResponse();
        break;
}

header($response['status_code_header']);
if ($response['body']) {
    echo $response['body'];
}


function setHighScore($player,   $userName, $score)
{
    $result = $player->setHighScore($score, $userName);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}


function setLastGame($player, $userName, $score)
{

    $result = $player->setLastGame($score, $userName);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function getHighScore($player, $userName)
{
    // echo $userName;
    $result = $player->getHighScore($userName);
    if (!$result) {
        return notFoundResponse();
    }
    // $result = 'hi';

    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    // echo $result;
    return $response;
}



function unprocessableEntityResponse()
{
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode([
        'error' => 'Invalid input',
    ]);
    return $response;
    //}
}
function notFoundResponse()
{
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';


    $response['body'] = null;
    return $response;
}
