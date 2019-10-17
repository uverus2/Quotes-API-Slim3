<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App;

require 'config/db.php';

$container = $app->getContainer();

$container['db'] = function () {
    $conn = new PDO("mysql:host=localhost;dbname=users", "root", "123456");
    return $conn;
};

$app->get("/api/quotes" , function ( Request $request, Response $response, array $args){
     $stm = $this->db->query("SELECT * FROM quotes");
     $row = $stm->fetchAll(PDO::FETCH_ASSOC);
     return $response->withJson($row);
     
});


$app->get("/api/quote/{author}" , function ( Request $request, Response $response, array $args){
     $author = $args["author"];
     $stm = $this->db->prepare("SELECT * FROM quotes WHERE author=:author");
     $stm->bindParam(":author", $author);
     $stm->execute();
     $row = $stm->fetchAll(PDO::FETCH_ASSOC);
     if ( empty($row) ) {
         $message = '{"message":"Quote Does not exist"}';
         return $response->withJson($message)
            ->withStatus(404);
     }else {
        return $response->withJson($row);
     }
     
});

$app->post("/api/quote/add" , function ( Request $request, Response $response, array $args){

    $quote = $request->getParam("quote");
    $author = $request->getParam("author");
    $year = $request->getParam("year");

    $stm = $this->db->prepare("INSERT INTO quotes (quote, author, year) VALUES ( :quote, :author, :year)");
    
        $stm->bindParam(":quote", $quote);
        $stm->bindParam(":author", $author);
        $stm->bindParam(":year", $year);

        if(!$stm->execute()) {
            $message = json_encode("message:Quote Not Added. Internal Server Error");
            return $response->withJson($message)
            ->withStatus(500);
        }else {
            $message = json_encode("message:Quote Succesfully Added");
            return $response->withJson($message);
        } 
});


$app->put("/api/quote/update/{id}" , function ( Request $request, Response $response, array $args){

    
    $id = $args["id"];
    $put = json_decode($request->getBody()->getContents(), true);

    $quote = $put["quote"];
    $author = $put["author"];
    $year = $put["year"];

    $stm = $this->db->prepare("UPDATE quotes SET quote=:quote, author=:author, year=:year WHERE ID=:id ");
        $stm->bindParam(":id", $id);
        $stm->bindParam(":quote", $quote);
        $stm->bindParam(":author", $author);
        $stm->bindParam(":year", $year);

        if($stm->execute()) {
            $message = json_encode("message:Quote Succesfully Updated");
            return $response->withJson($message);
        }else {
            $message = json_encode("message:Quote Not Updated. Internal Server Error");
            return $response->withJson($message)
            ->withStatus(500);
        } 
});


$app->delete("/api/quote/delete/{id}" , function ( Request $request, Response $response, array $args){

    $id = $args["id"];
    $stm = $this->db->prepare("DELETE FROM quotes WHERE ID=:id ");
    $stm->bindParam(":id", $id);
    
        if($stm->execute()) {
            $message = json_encode("message:Quote Succesfully Deleted");
            return $response->withJson($message);
        }else {
            $message = json_encode("message:Quote Not Deleted. Internal Server Error");
            return $response->withJson($message)
            ->withStatus(500);
        } 
});




?>