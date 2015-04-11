<?php
require 'Slim/Slim.php';
define('TIMEZONE', 'Asia/Ho_Chi_Minh');
date_default_timezone_set(TIMEZONE);
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim(array(
    'debug' => true
));

//$app->get('/session/:uid/:sid', 'validateSession');
$app->post('/login', 'login');
$app->get('/friends', 'authenticateSession','getFriends');
$app->get('/friend/:id', 'authenticateSession', 'getFriend');
$app->post('/friends', 'authenticateSession','addFriend');
$app->put('/friend/:id','authenticateSession', 'updateFriend');
$app->delete('/friend/:id', 'authenticateSession', 'deleteFriend');
$app->get('/friend/search/:query', 'authenticateSession', 'findByName');

$app->run();

//curl -i -X POST -H 'Content-Type: application/json' -d '{"username":"ntkthoa","password":"123456"}' http://127.0.0.1:8080/php-service/login
//Check  user/pass
function login() {
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
        $user = $stmt->fetchObject();
        $db = null;
        if($user) {
            $userSession = checkSession($user->id);
            echo json_encode($userSession);
            if ($userSession) {
                updateSession($userSession->user_id);
                echo json_encode("session existing: ".$userSession->session_id);
                $uID = $userSession->user_id;
                $sID = $userSession->session_id;
            } else {
                echo json_encode($user->id);
                echo json_encode($data->username . $data->password);
                if (generateSession($user->id, $data->username . $data->password)) {
                    $uID = $user->id;
                    $sID = $data->username . $data->password;
                }
            }
            setSessionOnBrowser($uID, $sID);
        } else {
            echo json_encode(false);
            return false;
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return "error";
    }
}


//Set session for browser
function setSessionOnBrowser($user_id, $session_id) {
    $app = \Slim\Slim::getInstance();
    try {
        $app->setEncryptedCookie('user_id', $user_id, '1 minutes');
        $app->setEncryptedCookie('session_id', $session_id, '1 minutes');
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
};

// route API to authenticate
function authenticateSession(\Slim\Route $route) {
    $app = \Slim\Slim::getInstance();
    $user_id = $app->getEncryptedCookie('user_id');
    $session_id = $app->getEncryptedCookie('session_id');
    if (validateSession($user_id, $session_id) === false) {
        $app->halt(401);
    }
}

//Check session valid or not
function validateSession($user_id, $session_id){
    $sql = "SELECT * FROM last_login_tb WHERE user_id=:user_id and session_id=:session_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->bindParam("session_id", $session_id);
        $stmt->execute();
        if ($hello = $stmt->fetchObject()) {
            $db = null;

            $tz_object = new DateTimeZone('Asia/Ho_Chi_Minh');
            $sysdate = new DateTime();
            $sysdate->setTimezone($tz_object);
            $dbdate = new DateTime($hello->last_login_time);
            $dbdate->setTimezone($tz_object);

            echo json_encode($sysdate);
            echo json_encode($dbdate);

            $since_start = $sysdate->diff($dbdate);
            $minutes = $since_start->days * 24 * 60;
            $minutes += $since_start->h * 60;
            $minutes += $since_start->i;

            echo json_encode($minutes);

            //TODO: if $minutes > 10 delete session else updateSession()
            if ($minutes > 1) {
                return false;
            } else {
                updateSession($user_id);
            }
            return true;
        } else {
            return false;
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return false;
    }
}

//Update lifetime for sesssion
function updateSession($user_id) {
    $sql = "UPDATE last_login_tb SET last_login_time=:last_login_time where user_id=:user_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);

        $tz_object = new DateTimeZone('Asia/Ho_Chi_Minh');
        $sysdate = new DateTime();
        $sysdate->setTimezone($tz_object);
        $caldate = $sysdate->format('Y-m-d H:i:s');
        $stmt->bindParam("last_login_time", $caldate);

        $stmt->bindParam("user_id", $user_id);
        $stmt->execute();
        $db = null;
        echo json_encode("success");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Delete session
function deleteSession($user_id) {
    $sql = "DELETE FROM last_login_tb WHERE user_id=:user_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->execute();
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Generate session
function generateSession($user_id, $session_id) {
    $sql = "INSERT INTO last_login_tb (user_id, session_id, last_login_time) VALUES (:user_id, :session_id, :last_login_time)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->bindParam("session_id", $session_id);

        $tz_object = new DateTimeZone('Asia/Ho_Chi_Minh');
        $sysdate = new DateTime();
        $sysdate->setTimezone($tz_object);
        echo json_encode(new DateTime());
        $caldate = $sysdate->format('Y-m-d H:i:s');
        $stmt->bindParam("last_login_time", $caldate);

        $stmt->execute();
        if($db->lastInsertId()) {
            echo json_encode("new: ".$session_id);
            return true;
            //setSessionOnBrowser($user_id, $session_id);
        } else {
            echo json_encode(false);
            return false;
        }
        $db = null;
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return "error";
    }
}

//Check session
function checkSession($user_id){
    $sql = "SELECT * FROM last_login_tb WHERE user_id=:user_id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("user_id", $user_id);
        $stmt->execute();
        if ($sessionObject = $stmt->fetchObject()) {
            $db = null;
            return $sessionObject;
        } else {
            return false;
        }
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
        return "error";
    }
}

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
    $dbh->exec("SET time_zone='Asia/Ho_Chi_Minh'");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}