<?php
function getUserName($user_id, $conn) {
    $sql = "SELECT name FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id); // 'i' is for integer type
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();

    return $name ? htmlspecialchars($name) : "Guest";
}
?>
