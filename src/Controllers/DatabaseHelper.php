<?php
/**
 * Database Helper Functions
 * Functions for standardized interaction with the database
 */

/**
 * Prepare a datetime value for insertion into the database
 * Ensures consistent datetime format across the application
 * 
 * @param string|null $datetime Optional datetime string, defaults to current time
 * @return string Formatted datetime string ready for database insertion
 */
function prepareDateTime($datetime = null) {
    if ($datetime === null) {
        // Current time in the application's timezone
        $date = new DateTime();
    } else {
        // Parse the provided datetime string
        $date = new DateTime($datetime);
    }
    
    // Format for MySQL (YYYY-MM-DD HH:MM:SS)
    return $date->format('Y-m-d H:i:s');
}

/**
 * Convert database datetime to local timezone for display
 * 
 * @param string $dbDatetime Datetime from database
 * @param string $format Output format
 * @return string Formatted datetime in local timezone
 */
function dbDateTimeToLocal($dbDatetime, $format = 'Y-m-d h:i:s A') {
    if (empty($dbDatetime)) return '';
    
    $date = new DateTime($dbDatetime);
    return $date->format($format);
}
?>
