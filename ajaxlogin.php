<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();
function help_redirect($url)
{
    if (headers_sent() === false)
    {
        header('Location: ' . $url, true);
    }
}

$hdr = '<!DOCTYPE html>
        <html lang = "en">
          <head>
            <meta charset="UTF-8">
            <title>content 1 PHP</title>
            <link rel='."stylesheet".' href='."./style.css".' type='."text/css".' media='."all".' />
            <script src='."login.js".'></script>
          </head>
          <body>
          <div id="content">';

$ftr = '    
    <form action="./logout.php"
      method = "post">
      <p><input type="submit" value="Logout" name="Logout" class="redbtn"></p>
    </form>
    </div>
  </body>
</html>';

function killsession(){
    $_SESSION = array();
    session_destroy();
    $path = explode('/', $_SERVER['PHP_SELF'], -1);
    $path = implode('/', $path);
    $path = "http://$_SERVER[HTTP_HOST]$path/index.php";
    help_redirect($path);
    die();
    exit();
}

include 'pass.php';
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "rousee-db", $db_pass, "rousee-db");
if ($mysqli->connect_errno){
    echo "Database connection failed: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
} 

function check_user($mysqli, $type){
    $name = $_POST['username'];
    $pass = $_POST['password'];


    if(!($stmt = $mysqli->prepare("SELECT name FROM user WHERE name = ? ORDER BY name
            "))){
        echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
    }
    if(!$stmt->bind_param("s", $name)){
        echo "Bind failed: " .$stmt->errno." ".$stmt->error;
    }
    if(!$stmt->execute()){
        echo "Execute failed: " .$stmt->errno." ".$stmt->error;
    }
    $stmt->store_result();

    if($stmt->num_rows !== 0 && $type === "new"){
        echo 1;
        return "Someone is using that name already. Please try a different username; it must be unique.<br>";
    }

    if($stmt->num_rows === 0 && $type === "old"){
        echo 2;
        return "I can't find that username...did you type it right? It is case sensitive.<br>";
    }

    $stmt->close();
    return true;
}

function check_pass($mysqli){
    $name = $_POST['username'];
    $pass = $_POST['password'];


    if(!($stmt = $mysqli->prepare("SELECT name, pass FROM user WHERE name = ?
            "))){
        echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
    }
    if(!$stmt->bind_param("s", $name)){
        echo "Bind failed: " .$stmt->errno." ".$stmt->error;
    }
    if(!$stmt->execute()){
        echo "Execute failed: " .$stmt->errno." ".$stmt->error;
    }
    if(!$stmt->bind_result($name, $retd)){
        echo "Bind failed: " .$stmt->errno." ".$stmt->error;
    }
    $stmt->fetch();
    $stmt->close();

    if ($retd != $pass){
        echo 3;
        return "The password you entered is incorrect.";
    }

    return true;
}


function add_user($mysqli){
    $name = $_POST['username'];
    $pass = $_POST['password'];
    $valid = check_user($mysqli, "new");

    if ($valid === true){
        
        if(!($stmt = $mysqli->prepare("INSERT INTO user(id, name, pass)
                    VALUES (null, ?, ?)
                    "))) {
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->bind_param("ss", $name, $pass)){
            echo "Bind failed: " .$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }

        $stmt->close();
        return true;

    }

    return $valid;

}

$content = "";

//verify username && password have been entered
if(isset($_POST['username']) && $_POST['username'] != null &&
    isset($_POST['password']) && $_POST['password'] != null) {
    if(session_status() == PHP_SESSION_ACTIVE){
        $_SESSION['username'] = $_POST['username'];

        if (!empty($_POST['create']) && $_POST['create'] == 'create_new'){
            $temp = add_user($mysqli);
            if ($temp === true){
                $content = $content."You've been successfully added."."<br>";
                echo 0;
                if(!isset($_SESSION['active'])){
                    $_SESSION['active'] = true;
                } 
            } else {
                $content = $content.$temp."<br>";
            }
        }

        if (!empty($_POST['login']) && $_POST['login'] == 'login_user'){
            $temp1 = check_user($mysqli, "old");
            $temp2 = check_pass($mysqli);

            if ($temp1 !== true){
                $content = $content.$temp1."<br>";
            } else if ($temp2 !== true){
                $content = $content.$temp2."<br>";
            } else {
                $content = $content."Welcome Back!"."<br>";
                echo 0;
                if(!isset($_SESSION['active'])){
                    $_SESSION['active'] = true;
                } 
            }
        }

 

    }
    
} elseif(isset($_POST['logout']) && $_POST['logout'] == "Logout") {
    killsession();

} elseif (isset($_POST['username']) && $_POST['username'] == null) {
    $content = $content."A username must be entered. Click <a href=".'"./">'."here</a> to return to login screen."."<br>";
    killsession();

} elseif (isset($_POST['password']) && $_POST['password'] == null) {
    $content = $content."A password must be entered. Click <a href=".'"./">'."here</a> to return to login screen."."<br>";
    killsession();

}


//actual view generation...
if(isset($_SESSION['active']) && $_SESSION['active']) {
    $content = $content."Hello $_SESSION[username]!<br>"."<br>";
} else {
    $ftr = '    
    <form action="./logout.php"
      method = "post">
      <p><input type="submit" value="Go Back" name="logout" class="redbtn"></p>
    </form>
    </div>
  </body>
</html>';
}
/*echo $hdr;
echo $content;
echo $ftr;*/
?>
    
