<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(); 

$app->get('/hello', 'getHello');

$app->run();

function getHello() {
    $sql = "SELECT id, name, job FROM friends";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $hello = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"hello": ' . json_encode($hello) . '}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="vertrigo";
    $dbname="slim_db";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}