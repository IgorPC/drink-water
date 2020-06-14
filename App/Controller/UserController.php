<?php

namespace App\Controller;

use App\Controller\TokenController;
use App\Controller\DB;
use Laminas\Diactoros\Response\JsonResponse;
use Lcobucci\JWT\Token;
use PhpParser\Node\Stmt\Echo_;
use Laminas\Diactoros\ServerRequest;
use function HighlightUtilities\splitCodeIntoArray;

class UserController
{

    public function index()
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $db = (new DB())->getDB();
        $queryVerify = "SELECT * FROM users";
        $result = mysqli_query($db, $queryVerify) or die('Erro na query');
        while($user = $users = mysqli_fetch_assoc($result)){
            $data[] = $user;
        }
        return $data;
    }

    public function create()
    {
        try {
            $db = new DB();
            $data = $_REQUEST;
            if(array_key_exists("email", $data) == false || $data['email'] == ''){
                $data =  ['data' => ['status_code' => 400, 'Message' => 'email fields is required!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
                return $response;
            }
            if(array_key_exists("name", $data) == false || $data['name'] == ''){
                $data =  ['data' => ['status_code' => 400, 'Message' => 'name fields is required!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
                return $response;
            }
            if(array_key_exists("password", $data) == false || $data['password'] == ''){
                $data =  ['data' => ['status_code' => 400, 'Message' => 'password fields is required!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
                return $response;
            }
            $user = $db->select('*', 'users', 'email', $data['email']);
            if($data['email'] == $user['email']){
                $data =  ['data' => ['status_code' => 400, 'Message' => 'Email is already registered!']];
                $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
                return $response;
            }else{
                $insert = "NULL, '{$data['name']}', '{$data['email']}', '{$data['password']}', '0'";
                $db->insert('users', $insert);
                $data =  ['data' => ['status_code' => 201, 'Message' => 'Success!']];
                $response = new JsonResponse($data, 201, ['Content-Type' => ['application/hal+json']]);
                return $response;
            }
        }catch (\Exception $e){
            return ['msg' => $e->getMessage()];
        }
    }

    public function show($id)
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $db = new DB();
        $userID = $id->getAttributes();
        $user = $db->select('*', 'users', 'id', $userID['id']);
        if($user == null){
            $data =  ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            $data =  ['data' => [
                'status_code' => 200,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'drink_counter' => $user['drink_counter']
                ]
            ]];
            $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
    }

    public function login()
    {
        $data = $_REQUEST;
        if(array_key_exists("email", $data) == false || $data['email'] == ''){
            $data =  ['data' => ['status_code' => 400, 'Message' => 'email fields is required!']];
            $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
        if(array_key_exists("password", $data) == false || $data['password'] == ''){
            $data =  ['data' => ['status_code' => 400, 'Message' => 'password fields is required!']];
            $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
        $db = new DB();
        $user = $db->raw("SELECT * FROM users WHERE email = '{$data['email']}' and password = '{$data['password']}'");
        if($user == null){
            $data =  ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            $token = (new TokenController())->generateToken($user['email']);
            $data =  ['data' => [
                'status_code' => 202,
                'Token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'drink_counter' => $user['drink_counter']
                ]
            ]];
            $response = new JsonResponse($data, 202, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
    }

    public function update($id)
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $email = explode(' ', $header['HTTP_AUTHORIZATION']);

        global $_PUT;

        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'PUT')) {
            parse_str(file_get_contents('php://input'), $_PUT);
        }

        $userID = $id->getAttributes();
        $data = $_PUT;

        $db = new DB();
        $user = $db->select('*', 'users', 'id', $userID['id']);
        if($user == null){
            $data =  ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }elseif ($user['email'] != (new TokenController())->getClaim($email[1])){
            $data =  ['data' => ['status_code' => 401, 'Message' => 'The authenticated user is different from the target!']];
            $response = new JsonResponse($data, 401, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            if(array_key_exists("name", $data) == true ){
                $db->update('users', 'name', $data["name"], 'id', $user['id']);
            }
            if (array_key_exists("email", $data) == true){
                $user = $db->select('*', 'users', 'email', $data['email']);
                if($user['email'] != null){
                    $data =  ['data' => ['status_code' => 400, 'Message' => 'Email is already registered!']];
                    $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
                    return $response;
                }
                $db->update('users', 'email', $data['email'], 'id', $userID['id']);
            }
            if(array_key_exists("password", $data) == true){
                $db->update('users', 'password', $data['password'], 'id', $userID['id']);
            }
        }
        $data =  ['data' => ['status_code' => 200, 'Message' => 'Success!']];
        $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
        return $response;
    }

    public function destroy($id)
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $email = explode(' ', $header['HTTP_AUTHORIZATION']);

        global $_DELETE;

        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'DELETE')) {
            parse_str(file_get_contents('php://input'), $_DELETE);
        }

        $db = new DB();
        $userID = $id->getAttributes();

        $user = $db->select('*', 'users', 'id', $userID['id']);
        if($user == null){
            $data =  ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }elseif ($user['email'] != (new TokenController())->getClaim($email[1])){
            $data =  ['data' => ['status_code' => 401, 'Message' => 'The authenticated user is different from the target!']];
            $response = new JsonResponse($data, 401, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            if($user['drink_counter'] != 0){
                $db->delete('drink_history', 'user_id', $user['id']);
            }
            $db->delete('users', 'id', $user['id']);
            $data = ['data' => ['status_code' => 200, 'Message' => 'User removed successfully!']];
            $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
    }
}