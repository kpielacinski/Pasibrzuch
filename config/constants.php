<?php 
    session_start();

    define('SITEURL', 'http://localhost/Pasibrzuch/');
    define('LOCALHOST', 'localhost');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    define('DB_NAME', 'pasibrzuchdb');
    
    $conn = mysqli_connect(LOCALHOST, DB_USERNAME, DB_PASSWORD) or die(mysqli_error($conn));
    $db_select = mysqli_select_db($conn, DB_NAME) or die(mysqli_error($conn));

    function pdo_connect_mysql() {
        $dsn = 'mysql:dbname=pasibrzuchdb;host=localhost';
        $user = 'root';
        $password = '';
        try {
            return new PDO($dsn, $user, $password);
        } catch (PDOException $exception) {
            // If there is an error with the connection, stop the script and display the error.
            exit('Failed to connect to database!');
        }
    }
?>