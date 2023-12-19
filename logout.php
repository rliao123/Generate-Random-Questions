<?php
require_once 'login.php';

$conn = new mysqli($hn, $un, $pw, $db); // create connection
if ($conn->connect_error)
    die(error_message());

session_start();
define("ONE_MONTH", 2592000);

if (isset($_POST['sign_out'])) { // if sign out button is clicked, direct user to sign up/sign in page
    header("Location: Final.php");
    destroy_session();
}

$conn->close();

function destroy_session()
{
    $_SESSION = array();
    setcookie(session_name(), '', time() - ONE_MONTH, '/');
    session_destroy();
}

?>