<?php
require_once 'login.php';

$conn = new mysqli($hn, $un, $pw, $db); // create connection
if ($conn->connect_error)
    die(error_message());

session_start();
define("ONE_MONTH", 2592000);

$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
if ($_SESSION['ip'] != $_SERVER['REMOTE_ADDR']) {
    different_user();
}

if (! isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = 1;
}

if (! isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
} else {
    ++ $_SESSION['count'];
}

if (isset($_SESSION['username'])) {

    $username = sanitize_string($conn, $_SESSION['username']);

    // INPUT QUESTIONS
    echo <<<_END
    <header><h1 style="color: #1c90ff;">Welcome!</h1></header>
    <table border="0" cellpadding="2" cellspacing="5" bgcolor="#ffe4d5">
        <th colspan="2" align="center">Submit Questions</th>
         <form method="post" action="home.php" enctype='multipart/form-data'>
            <tr><td>Questions File</td>
                <td><input type='file' name='filename' size='10'></td></tr>
            <tr><td colspan="2" align="center"><input type="submit" value="Submit" style="margin-top: 10px; font-size: 15px"></td></tr>
         </form>
    </table>
    _END;

    // SIGN OUT BUTTON
    echo <<<_END
        <div style="margin-top: 30px;"><form method='post' action='logout.php' enctype='multipart/form-data'>
            <input type='submit' value='Sign Out' name='sign_out'>
        </form></div>
    _END;

    // GENERATE QUESTION BUTTON
    echo <<<_END
        <hr><div style="margin-top: 30px;"><form method='post' action='home.php' enctype='multipart/form-data'>
            <input type='submit' style='background-color: #ffe4d5; font-size: 18px' value='Generate Random Question' name='generate'>
        </form></div>
    _END;

    if (isset($_FILES['filename'])) { // check that user upload file

        if ($_FILES['filename']['type'] == 'text/plain') { // check type of file
            $ext = 'txt';
        } else {
            $ext = '';
        }

        if ($ext) {

            echo "Uploaded file!<br>";

            $tmp_name = sanitize_string($conn, $_FILES['filename']['tmp_name']); // use tmp_name to open file

            $content = read_content($tmp_name); // read and sanitize the contents of the file

            $content = sanitize_string($conn, $content); // further sanitize contents of file

            $lines = explode('\n', $content); // separate lines in the file, put questions into array

            foreach ($lines as $line) {

                $query = "SELECT question FROM questions WHERE username='$username' AND question='$line'"; // check if question already exists for the user
                $result = $conn->query($query);
                if (! $result) {
                    echo error_message();
                }

                if ($result->num_rows == 0) { // check for duplicates before inserting

                    $query = "INSERT INTO questions VALUES" . "('$username','$line')"; // insert questions
                    $result = $conn->query($query);
                    if (! $result) {
                        echo error_message();
                    }
                }
            }
            $result->close();
        } else {
            echo "Invalid file. Please upload an acceptable file.";
        }
    }

    if (isset($_POST['generate'])) { // if user click generate random question

        $query = "SELECT question FROM questions WHERE username='$username'"; // check that there are questions for the user in databse
        $result = $conn->query($query);
        if (! $result)
            die(error_message());

        if ($result->num_rows > 0) { // make sure user has questions

            $questions_array = array();

            while ($row = $result->fetch_assoc()) { // add all the user's questions into an array
                $questions_array[] = htmlspecialchars_decode($row['question']); // to make sure questions with special characters are printed correctly
            }
            $result->close();

            $random = $questions_array[array_rand($questions_array)]; // get a random questions
            echo $random . "<br>";
        } else {
            echo "No questions found.";
        }
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
 * Read the content of the file that is uploaded
 * @return string of content
 */
function read_content($tmp_name)
{
    $fh = fopen($tmp_name, 'r+') or die("File does not exist or permission issues");

    if ($fh) {
        $result = htmlentities(file_get_contents("$tmp_name")); // read entire file
    }
    fclose($fh);

    return $result;
}

/*
 * Destroy session when user click sign out or if user redirected to log in page
 */
function destroy_session()
{
    $_SESSION = array();
    setcookie(session_name(), '', time() - ONE_MONTH, '/');
    session_destroy();
}

/*
 * Redirect user to log in page
 */
function different_user()
{
    header("Location: Final.php");
    destroy_session();
}
?>