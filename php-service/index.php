<?php
require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
    'debug' => true
));

$app->get('/session/:id', 'checkSession');
$app->post('/login', 'checkAuthentication');
$app->get('/authenticate', 'generateAPIKey');
$app->get('/friends', 'authenticate','getFriends');
$app->get('/friend/:id',  'getFriend');
$app->post('/friends', 'addFriend');
$app->put('/friend/:id', 'updateFriend');
$app->delete('/friend/:id', 'deleteFriend');
$app->get('/friend/search/:query', 'findByName');

$app->run();

//curl -i -X POST -H 'Content-Type: application/json' -d '{"username":"ntkthoa","password":"123456"}' http://127.0.0.1:8080/php-service/login
function checkAuthentication() {
    $request = \Slim\Slim::getInstance()->request();
    $body = $request->getBody();
    $data = json_decode($body);
    $sql = "SELECT * FROM user_tb WHERE user_name=:username and pass_word=:password";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("username", $data->username);
        $stmt->bindParam("password", $data->password);
        $stmt->execute();
        $hello = $stmt->fetchObject();
        $db = null;
        if($hello) {
            $status = checkSession($hello->id);
            if ($status == null) {
                generateSession($hello->id, $data->username . $data->password);
            } else {
                echo json_encode("existing: ".$status);
            }
        } else {
            echo json_encode(false);
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function generateSession($user_id, $session_id) {
    $sql = "INSERT INTO last_login_tb (user_id, session_id) VALUES (:user_id, :session_id)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        if($db->lastInsertId()) {
            echo json_encode("new: ".$session_id);
        } else {
            echo json_encode(false);
        }
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

function checkSession($user_id){
    $sql = "SELECT * FROM last_login_tb WHERE user_id=:user_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->execute();
        if ($hello = $stmt->fetchObject()) {
            $db = null;
            return $hello->session_id;
        } else {
            return null;
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

// route middleware for simple API authentication
function authenticate(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    $uid = $app->getEncryptedCookie('uid');
    $key = $app->getEncryptedCookie('key');
    if (validateUserKey($uid, $key) === false) {
      $app->halt(401);
    }
}

function validateUserKey($uid, $key) {
  // insert your (hopefully more complex) validation routine here
  if ($uid == 'demo' && $key == 'demo') {
    return true;
  } else {
    return false;
  }
}

// generates a temporary API key using cookies
// call this first to gain access to protected API methods
function generateAPIKey() {
$app = \Slim\Slim::getInstance();
  try {
    $app->setEncryptedCookie('uid', 'demo', '5 minutes');
    $app->setEncryptedCookie('key', 'demo', '5 minutes');
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
};

function getFriends() {
    $sql = "SELECT id, name, job FROM friends";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);
        $hello = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        echo '{"friends": ' . json_encode($hello) . '}';
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