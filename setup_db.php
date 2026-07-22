<?php
// Setup script to initialize the database
$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    
    if ($sql === false) {
        die("Error reading schema.sql file.");
    }
    
    // Execute the SQL
    $pdo->exec($sql);
    echo "Database schema initialized successfully!\n";
    
} catch(PDOException $e) {
    die("Database setup failed: " . $e->getMessage());
}
?>
