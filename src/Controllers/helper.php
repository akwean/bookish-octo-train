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

/**
 * Format a database datetime value according to the local timezone
 * 
 * @param string $datetime The datetime string from the database
 * @param string $format The desired output format (optional)
 * @return string Formatted datetime string
 */
function formatDateTime($datetime, $format = 'Y-m-d h:i:s A') {
    if (!$datetime) return '';
    
    $date = new DateTime($datetime);
    return $date->format($format);
}

/**
 * Format a database date value
 * 
 * @param string $date The date string from the database
 * @param string $format The desired output format (optional)
 * @return string Formatted date string
 */
function formatDate($date, $format = 'F d, Y') {
    if (!$date) return '';
    
    $dateObj = new DateTime($date);
    return $dateObj->format($format);
}
?>
