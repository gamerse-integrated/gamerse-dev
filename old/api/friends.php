<?php
 
 
 // header("Access-Control-Allow-Origin: *");
 // header("Content-Type: application/json; charset=UTF-8");
 // header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
 include_once '../config/Database.php';
 include_once '../models/Friends.php';
 
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
$friends = new Friends($db);

$requestMethod = $_SERVER["REQUEST_METHOD"];
// $uri = parse_url($_SERVER['REQUEST_URI']);
// $uri = $_SERVER['REQUEST_URI'];
$query_str = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($query_str, $uri);
// print_r($uri);
// $uri = explode('/', $uri);
$userName = null;
$friendName = null;
$friendRecordId = null;
$action = null;
$fr = null;
$id = null;
$friendid = null;

if (isset($uri['userName'])) {
    $userName = $uri['userName'];
}
if (isset($uri['fr'])) {
    $fr = $uri['fr'];
}
if (isset($uri['id'])) {
    $id = $uri['id'];
}

// for post data
$input = (array) json_decode(file_get_contents('php://input'), true);


if (isset($input['action'])){
    $action = $input['action'];
}
if (isset($input['friendRecordId'])){
    $friendRecordId = $input['friendRecordId'];
}
if (isset($input['friendName'])){
    $friendName = $input['friendName'];
}
if (isset($input['userName'])){
    $userName = $input['userName'];
}
if (isset($input['friendid'])){
    $friendid = $input['friendid'];
}
if (isset($input['message'])){
    $message = $input['message'];
}

// echo($friendRecordId);

// print_r($uri);
// echo("userName: ".$userName);



switch ($requestMethod) {
    case 'GET':  
        if (isset($fr)) $response = findFriendRequests($friends, $userName);
        else if (isset($id)) $response = getChatMessages($friends, $id);          
        else $response = getAllFriends($friends,$userName);          
        break;
    case 'POST':
        if(isset($friendName) && isset($userName)){
            // echo("if "); 
            $response = sendFriendReq($friends,$userName,$friendName);
        }
        else if(isset($message)) {
            $response = addMessage($friends,$friendid,$message);
        }
        else{
            echo("else "); 
            if ($action == 'A'){
                // echo("Accept");
                $response = addFriend($friends,$friendRecordId);
            }
            else if($action == 'R'){
                // echo("Reject");
                $response = rFriend($friends,$friendRecordId);
            }        
        }
        
        break;
    case 'PUT':
        $response = updateUser($friends,$userName);
        break;
    case 'DELETE':
        $response = deleteUser($friends,$userName);
        break;
    default:
        $response = notFoundResponse();
        break;
}

header($response['status_code_header']);
if ($response['body']) {
    echo $response['body'];
}




function getAllFriends($friends,$userName)
{
    $result = $friends->findAllFriends($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function findFriendRequests($friends,$userName)
{
    $result = $friends->findFriendRequests($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function addMessage($friends,$friendid, $message)
{
    $result = $friends->addMessage($friendid, $message);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function getChatMessages($friends, $id)
{
    $result = $friends->getChatMessages($id);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}


function addFriend($friends,$friendRecordId)
{
    $result = $friends->addFriend($friendRecordId);
    if (!$result) {
        return notFoundResponse();
    }
    // echo "found response";
    // echo $friendRecordId;
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}
function sendFriendReq($friends,$userName,$friendName)
{
    $result = $friends->sendFriendReq($userName,$friendName);
    if (!$result) {
        return notFoundResponse();
    }
    // echo "found response";
    // echo $friendRecordId;
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function rFriend($friends,$friendRecordId)
{
    $result = $friends->rFriend($friendRecordId);
    if (!$result) {
        return notFoundResponse();
    }
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
}

function getUser($friends,$userName)
{
    // echo $userName;
    $result = $friends->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    // $result = 'hi';
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    
    return $response;
}


function createUser($friends)
{

    $input = (array) json_decode(file_get_contents('php://input'), true);
    // print_r($input);
    if (!validatePerson($input)) {
        return unprocessableEntityResponse();
    }
    print_r($input);

    //check if user exists
    // if exists , then return error
    // else create Friends
    $friends->insert($input);
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = null;
    return $response;
}

function updateUser($friends,$userName)
{
    $result = $friends->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $input = (array) json_decode(file_get_contents('php://input'), true);
    if (!validatePerson($input)) {
        return unprocessableEntityResponse();
    }
    $friends->update($userName, $input);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = null;
    return $response;
}

function deleteUser($friends,$userName)
{
    $result = $friends->find($userName);
    if (!$result) {
        return notFoundResponse();
    }
    $friends->delete($userName);
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