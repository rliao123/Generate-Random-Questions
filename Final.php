<?php
require_once 'login.php';

$conn = new mysqli($hn, $un, $pw, $db); // create connection
if ($conn->connect_error)
    die(error_message());

//echo "Hello!<br>";

echo <<<_END
<html>
    <head>
        <script type="text/javascript">
            function validate(form){
                fail = validateName(form.name.value)
                fail += validateUsername(form.username.value)
                fail += validatePassword(form.password.value)
                
                if (fail == ""){
                    return true
                } else {
                    alert(fail);
                    return false
                }
            }
            
            function validateName(field){
                if (field == ""){
                    return "No name was entered.\\n"
                } else {
                    return ""
                }
            }
            
            function validateUsername(field){
                if (field == ""){
                    return "No username was entered.\\n"
                } else if (field.length < 3){
                    return "Username must be at least 3 characters.\\n"
                }
                else if (!/[a-z]/.test(field) || !/[0-9]/.test(field)){
                    return "Username requires one of each: a-z,0-9.\\n"
                } else {
                    return ""
                }
            }
            
           function validatePassword(field){
                if (field == ""){
                    return "No password was entered.\\n"
                } else if (field.length < 6){
                    return "Password must be at least 6 characters.\\n"
                }
                else if (!/[a-z]/.test(field) || !/[A-Z]/.test(field) || !/[0-9]/.test(field)){
                    return "Password requires one of each: a-z, A-Z, 0-9.\\n"
                } else {
                    return ""
                }
            }
        </script>
    </head>
    <body>
    <header><h1 style="color: #643e66;">Hello!</h1></header>
    <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
        <th colspan="2" align="center">SIGN UP</th>
         <form method="post" action="Final.php" onsubmit="return validate(this);">
            <tr><td>Name</td>
                <td><input type="text" maxlength="32" name='name'></td></tr>
            <tr><td>Username</td>
                <td><input type="text" maxlength="32" name='username'></td></tr>
            <tr><td>Password</td>
                <td><input type="text" maxlength="32" name='password'></td></tr>
            <tr><td colspan="2" aligh="center"><input type="submit" value="Sign Up"></td></tr>
         </form>
    </table>
    <table border="0" cellpadding="2" cellspacing="5" bgcolor="#eeeeee">
        <th colspan="2" align="center">SIGN IN</th>
         <form method="post" action="Final.php" onsubmit="return validate(this);">
            <tr><td>Username</td>
                <td><input type="text" maxlength="9" name='user'></td></tr>
            <tr><td>Password</td>
                <td><input type="text" maxlength="32" name='pwd'></td></tr>
            <tr><td colspan="2" aligh="center"><input type="submit" value="Sign In"></td></tr>
         </form>
    </table>
    </body>
</html>
_END;

// $query = "DROP TABLE users";
// $result = $conn->query($query);

// $query = "DROP TABLE questions";
// $result = $conn->query($query);

// $query = "CREATE TABLE users(
// name VARCHAR(256),
// username VARCHAR(256),
// password VARCHAR(256)
// )";
// $result = $conn->query($query);
// if (! $result)
//     die(error_message());

// $query = "CREATE TABLE questions(
// username VARCHAR(256),
// question TEXT
// )";
// $result = $conn->query($query);
// if (! $result)
//     die(error_message());

if (isset($_POST['name']) && ! empty($_POST['name'])) {
    $name = sanitize_string($conn, $_POST['name']);
}
if (isset($_POST['username']) && ! empty($_POST['username'])) {
    $username = sanitize_string($conn, $_POST['username']);
}
if (isset($_POST['password']) && ! empty($_POST['password'])) {
    $password = sanitize_string($conn, $_POST['password']);
}

$fail = validate_name($name);
$fail .= validate_username($username);
$fail .= validate_password($password);

if ($fail == "") { // server side validation
                   // echo "Successfully validated!<br>";

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if (! $result)
        die(error_message());

    if ($result->num_rows > 0) { // make sure username is unique

        echo "Username is not available. Try again.<br>";
    } else {

        $token = password_hash($password, PASSWORD_DEFAULT); // apply salt to password and store within hash

        $query = "INSERT INTO users VALUES" . "('$name','$username','$token')"; // insert user into table
        $result = $conn->query($query);

        if (! $result) {
            echo error_message();
        }
    }
    $result->close();
}

if (isset($_POST['user']) && isset($_POST['pwd'])) { // when the user wants to sign in

    $user = sanitize_string($conn, $_POST['user']);
    $pw_temp = sanitize_string($conn, $_POST['pwd']);

    $query = "SELECT * FROM users WHERE username='$user'"; // check that user exists in databse
    $result = $conn->query($query);

    if (! $result)
        die(error_message());

    if ($result->num_rows) {

        $row = $result->fetch_array(MYSQLI_NUM);
        $result->close();

        $token = $row[2]; // get password from database
        $verified = password_verify($pw_temp, $token); // check if passwords are equal

        if ($verified) { // passwords match

            session_start();

            session_regenerate_id();

            $_SESSION['username'] = $user;

            header("Location: home.php"); // direct user to home page
        } else {
            echo "Invalid, please try again!";
        }
    } else {
        echo "Invalid, please try again";
    }
}

$conn->close();

/*
 * Sanitize string with htmlentities and escape special characters
 * @param $conn, $string
 * @return sanizitzed string
 */
function sanitize_string($conn, $string)
{
    return htmlentities(mysql_fix_string($conn, $string));
}

/*
 * Sanitize string by escaping special characters
 * @param $conn, $string
 * @return sanizitzed string
 */
function mysql_fix_string($conn, $string)
{
    return $conn->real_escape_string($string);
}

/*
 * Generic error message
 */
function error_message()
{
    return "Something went wrong";
}

/*
 * Validate name
 */
function validate_name($field)
{
    if ($field == "") {
        return "No name was entered.<br>";
    } else {
        return "";
    }
}

/*
 * Validate username: at least 3 characters, one of each: a-z, 0-9
 */
function validate_username($field)
{
    if ($field == "") {
        return "No username was entered.<br>";
    } else if (strlen($field) < 3) {
        return "Username must be at least 3 characters.<br>";
    } else if (! preg_match("/[a-z]/", $field) || ! preg_match("/[0-9]/", $field)) {
        return "Username requires one of each: a-z, 0-9.<br>";
    } else {
        return "";
    }
}

/*
 * Validate password: at least 6 characters, one of each: a-z, A-Z, 0-9
 */
function validate_password($field)
{
    if ($field == "") {
        return "No password was entered.<br>";
    } else if (strlen($field) < 6) {
        return "Password must be at least 6 characters.<br>";
    } else if (! preg_match("/[a-z]/", $field) || ! preg_match("/[A-Z]/", $field) || ! preg_match("/[0-9]/", $field)) {
        return "Password requires one of each: a-z, A-Z, 0-9.<br>";
    } else {
        return "";
    }
}

?>
