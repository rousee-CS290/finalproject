<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

session_start();
$hdr = '<!DOCTYPE html>
<html lang = "en">
  <head>
    <meta charset="UTF-8">
    <title>Video Store</title>
    <link rel="stylesheet" href="./style.css" type="text/css" media="all" />
  </head>
  <body>
  <div id="content">';
$ftr = '  </div>
  </body>
</html>';
// turn on error reporting
ini_set("display_errors", "On");
include "pass.php";
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "rousee-db", $db_pass, "rousee-db");

if ($mysqli->connect_errno){
    echo "Database connection failed: (" . $mysqli->connect_errno . ")" . $mysqli->connect_error;
} 
echo $hdr;
if (!isset($_SESSION['active']) || $_SESSION['active'] != true){
    echo '<h1>You must first <a href="./">log in</a> to view this page.</h1>';
    echo $ftr;
    exit();
}
echo "<h1>Welcome to your personal video collection, ".$_SESSION['username']."</h1>";
?>

<form action="./logout.php" 
    method = "post">
    <p><input type="submit" value="Logout" name="logout" class="redbtn"></p>
</form>

<form action="./video.php"
    method = "post">
    
    <fieldset>
        <legend>Add a Video</legend>
        <p>Name: <input type="text" name="name">
            Category: <input type="text" name="category">
            Length: <input type="text" name="length">
                    <p><input type="submit" name="AddVideo" value="add a video" class="grnbtn"></p>
    </fieldset>
    
</form>



<?php
$filter_val = 0;
function checkadd($mysqli){
    $name = $_POST['name'];
    $category = $_POST['category'];
    $length = $_POST['length'];
    $user = $_SESSION['username'];
    $errorIn_parms = False;
    if (!ctype_digit($length)){
        echo '<div id="error_message">Length must be an integer >= 0; not '.$length.'.<br></div>';
        $errorIn_parms = True;
    }
    if(empty($name)){
        echo '<div id="error_message">Name of Video is required.<br></div>';
        $errorIn_parms = True;
    } else {
        if(!($stmt = $mysqli->prepare("SELECT name, user FROM videos WHERE name = ? AND user = ? ORDER BY name
                "))){
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->bind_param("ss", $name, $user)){
            echo "Bind failed: " .$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }
        $stmt->store_result();
        if($stmt->num_rows != 0){
            echo '<div id="error_message">Name of Video must be unique.<br></div>';
            $errorIn_parms = True;
        }
        $stmt->close();
    }
    if($errorIn_parms){
        exit(0);
    }

}
//check request method
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    //delete all videos button
    if (!empty($_POST['Delete']) && $_POST['Delete'] == 'delete all videos'){
        if(!($stmt = $mysqli->prepare("DELETE
                FROM videos
                "))) {
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }

        $stmt->close();
    }
    //add a video section
    if (!empty($_POST['AddVideo']) && $_POST['AddVideo'] == 'add a video'){
        checkadd($mysqli);
        if(!($stmt = $mysqli->prepare("INSERT INTO videos(name, category, length, user)
                VALUES (?, ?, ?, ?)
                "))) {
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->bind_param("ssis", $_POST['name'], $_POST['category'], $_POST['length'], $_SESSION['username'])){
            echo "Bind failed: " .$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }

        $stmt->close();
    }
    //delete a video section
    if (!empty($_POST['DeleteRow']) && $_POST['DeleteRow'] == 'delete'){


        if(!($stmt = $mysqli->prepare("DELETE FROM videos WHERE id=?
                "))) {
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->bind_param("i", $_POST['DeleteRowID'])){
            echo "Bind failed: " .$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }

        $stmt->close();
    }
    //check in/out a video section
    if (!empty($_POST['CheckInOut']) && $_POST['CheckInOut'] == 'update'){


        if(!($stmt = $mysqli->prepare("UPDATE videos
                SET rented = !rented
                WHERE id = ?
                "))) {
            echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->bind_param("i", $_POST['CheckInOutID'])){
            echo "Bind failed: " .$stmt->errno." ".$stmt->error;
        }
        if(!$stmt->execute()){
            echo "Execute failed: " .$stmt->errno." ".$stmt->error;
        }

        $stmt->close();
    }
    //filter the video section
    if (!empty($_POST['filter'])){

        if ($_POST['filter'] === '_allmovies_') {
            $filter_val = 0;
        }
        else {
            $filter_val = $_POST['filter'];
        }
    }
}
//build the filter form
echo '<form action="./video.php"
        method = "post">
        <fieldset>
        <legend>Filter the Videos</legend>
        <select name="filter">
            <option value="_allmovies_">All Movies</option>';
            if(!($stmt = $mysqli->prepare("SELECT DISTINCT category FROM videos WHERE user = ? ORDER BY category"))){
                echo "Prepare failed: "  . $stmt->errno . " " . $stmt->error;
            }
            if(!$stmt->bind_param("s", $_SESSION['username'])){
                echo "Bind failed: " .$stmt->errno." ".$stmt->error;
            }
            if(!$stmt->execute()){
                echo "Execute failed: "  . $stmt->errno . " " . $stmt->error;
            }
            if(!$stmt->bind_result($category)){
                echo "Bind failed: "  . $stmt->errno . " " . $stmt->error;
            }
            while($stmt->fetch()){
             if($category != ""){echo '<option value="'. $category .'"> ' . $category . '</option>\n';}
            }
            $stmt->close();
//build the list
echo '</select>
        <input type="submit" name="FilterVideo" value="apply filter" class="blubtn">
    </fieldset>
    </form>';

    echo "<fieldset>
        <legend>Video List</legend>
        <table>
            
            <tr>
                <th>id#
                <th>Name
                <th>Category
                <th>Length
                <th>Checked out
                <th>Delete
                <th>Check-In/Check-Out
            </tr>";
            
            if ($filter_val === 0){
                echo "showing all movies";
                if(!($stmt = $mysqli->prepare("SELECT v.id, v.name, v.category, v.length, v.rented
                        FROM videos AS v WHERE user = ?
                        "))){
                    echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
                }
                if(!$stmt->bind_param("s", $_SESSION['username'])){
                    echo "Bind failed: " .$stmt->errno." ".$stmt->error;
                } 
            } else {
                echo "showing only $filter_val type movies";
                if(!($stmt = $mysqli->prepare("SELECT v.id, v.name, v.category, v.length, v.rented
                        FROM videos AS v WHERE user = ?
                        AND category = ?
                        "))){
                    echo "Prepare failed: :".$stmt->errno." ".$stmt->error;
                }
                if(!$stmt->bind_param("ss", $_SESSION['username'], $filter_val)){
                    echo "Bind failed: " .$stmt->errno." ".$stmt->error;
                }
            }
           
            if(!$stmt->execute()){
                echo "Execute failed: " .$stmt->errno." ".$stmt->error;
            }
            if(!$stmt->bind_result($id, $name, $category, $length, $rented)){
                echo "Bind failed: " .$stmt->errno." ".$stmt->error;
            }

            while($stmt->fetch()){
                echo "<tr>\n<td>\n".$id."\n<td>".$name."\n<td>".$category."\n<td>".$length."\n<td>";
                if($rented == '1' ){
                    $rented = 'loaned out';
                } else {
                    $rented = 'available';
                }
                echo $rented;
                echo '<td><form action="./video.php"
                          method = "post">
                          <input type="submit" name="DeleteRow" value="delete" class="redbtn">
                          <input type="hidden" name="DeleteRowID" value="'.$id.'">
                        </form>';
                echo '<td><form action="./video.php"
                          method = "post">
                          <input type="submit" name="CheckInOut" value="update" class="grnbtn">
                          <input type="hidden" name="CheckInOutID" value="'.$id.'">
                        </form>';
                echo "</tr>";
            }
            $stmt->close();
    echo "</table>
    </fieldset>";

    echo $ftr;
?>
