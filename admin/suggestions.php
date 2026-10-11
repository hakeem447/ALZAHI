<?php
// search-suggestions.php

// 1. Include database connection using absolute directory path
// NOTE: Change 'db.php' to your actual file name inside the config folder
include __DIR__ . '/api/config/db.php'; 

// 2. Get the search query from the AJAX request
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$suggestions = [];

if ($q !== '') {
    // Sanitize the input to prevent SQL Injection
    $q = mysqli_real_escape_string($conn, $q);
    
    // 3. Fetch unique customer names from the orders table
    $sql = "SELECT DISTINCT name FROM orders WHERE name LIKE '%$q%' LIMIT 5";
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $suggestions[] = $row['name']; 
        }
    }
}

// 4. Return results as JSON for JavaScript to process
header('Content-Type: application/json');
echo json_encode($suggestions);
exit();
?>
