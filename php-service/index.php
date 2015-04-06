<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/friends', 'getFriends');
$app->get('/friend/:id',  'getFriend');
$app->post('/friends', 'addFriend');
$app->put('/friend/:id', 'updateFriend');
$app->delete('/friend/:id', 'deleteFriend');
$app->get('/friend/search/:query', 'findByName');

$app->run();

function getFriends() {
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

function getFriend($id) {
    $sql = "SELECT * FROM friends WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $hello = $stmt->fetchObject();
        $db = null;
        echo json_encode($hello);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function addFriend() {
    $request = \Slim\Slim::getInstance()->request();
    $friend = json_decode($request->getBody());
    $sql = "INSERT INTO friends (name, job) VALUES (:name, :job)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $friend->name);
        $stmt->bindParam("job", $friend->job);
        $stmt->execute();
        $friend->id = $db->lastInsertId();
        $db = null;
        echo json_encode($friend);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function updateFriend($id) {
    $request = \Slim\Slim::getInstance()->request();
    $body = $request->getBody();
    $friend = json_decode($body);
    $sql = "UPDATE friends SET name=:name, job=:job WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("name", $friend->name);
        $stmt->bindParam("job", $friend->job);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo json_encode($friend);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function deleteFriend($id) {
    $sql = "DELETE FROM friends WHERE id=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function findByName($query) {
    $sql = "SELECT * FROM friends WHERE UPPER(name) LIKE :query ORDER BY name";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $query = "%".$query."%";
        $stmt->bindParam("query", $query);
        $stmt->execute();
        $friends = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"friends": ' . json_encode($friends) . '}';
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