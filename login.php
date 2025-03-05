<?php 

require_once 'connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) { 
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            header("Location: index.php");
            exit();
        } else {
            // Redirect back to index with error parameter
            header("Location: index.php?error=invalid");
            exit();
        }
    } else {
        // Redirect back to index with error parameter
        header("Location: index.php?error=invalid");
        exit();
    }

    $stmt->close();
}

?>