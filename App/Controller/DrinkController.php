<?php


namespace App\Controller;

use App\Controller\TokenController;
use App\Controller\DB;
use Laminas\Diactoros\Response\JsonResponse;

class DrinkController
{
    public function drink($id)
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $email = explode(' ', $header['HTTP_AUTHORIZATION']);

        $data = $_REQUEST;

        if(array_key_exists("drink_ml", $data) == false || $data['drink_ml'] == ''){
            $data =  ['data' => ['status_code' => 400, 'Message' => 'drink_ml fields is required!']];
            $response = new JsonResponse($data, 400, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
        $userID = $id->getAttributes();
        $db = new DB();

        $user = $db->select('*', 'users', 'id', $userID['id']);
        if($user == null){
            $data = ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }elseif ($user['email'] != (new TokenController())->getClaim($email[1])){
            $data = ['data' => ['status_code' => 401, 'Message' => 'The authenticated user is different from the target!']];
            $response = new JsonResponse($data, 401, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            $counter = $user['drink_counter'] + 1;
            $db->update('users', 'drink_counter', $counter, 'id', $user['id']);
            date_default_timezone_set('America/Sao_Paulo');
            $now = date('Y-m-d H:i:s');
            $drinkInsert = "NULL, '{$user['id']}', '{$data['drink_ml']}', '{$now}'";
            $db->insert('drink_history', $drinkInsert);
            $updatedUser = $db->select('*', 'users', 'id', $userID['id']);
            $data = ['data' => [
                'status_code' => 200, 'Message' => 'Data has been updated successfully!',
                'user' => [
                    'id' => $updatedUser['id'],
                    'name' => $updatedUser['name'],
                    'email' => $updatedUser['email'],
                    'drink_counter' => $updatedUser['drink_counter']
                ]
            ]];
            $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
    }

    public function historic($id)
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }

        $userID = $id->getAttributes();
        $db = new DB();
        $user = $db->select('*', 'users', 'id', $userID['id']);
        if($user == null){
            $data = ['data' => ['status_code' => 404, 'Message' => 'User not found!']];
            $response = new JsonResponse($data, 404, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }else{
            $query = "SELECT ml, data FROM drink_history WHERE user_id = '{$user['id']}'";
            $result = mysqli_query($db->getDB(), $query) or die('Erro na query');
            while($user = $historic = mysqli_fetch_assoc($result)){
                $data[] = $user;
            }
            $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
            return $response;
        }
    }

    public function rank()
    {
        $header = $_SERVER;

        $token = new TokenController();
        $error = $token->verifyHeader($header);
        if(!is_null($error)){
            return $error;
        }
        $db = new DB();

        date_default_timezone_set('America/Sao_Paulo');
        $now = date('Y-m-d');
        $query = "select user_id, sum(ml) from drink_history where data >= '{$now}' GROUP BY user_id ORDER BY `sum(ml)` DESC";
        $result = mysqli_query($db->getDB(), $query) or die('Erro na query');
        $historic = mysqli_fetch_assoc($result);
        $data = $historic;
        $response = new JsonResponse($data, 200, ['Content-Type' => ['application/hal+json']]);
        return $response;

    }
}