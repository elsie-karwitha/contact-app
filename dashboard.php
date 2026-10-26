<?php
session_start();

// Security Boundary: If user isn't logged in, boot them back to the login gateway
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection profiles matching our docker-compose config
    $host = 'db';
    $db   = 'contact_db';
    $user = 'root';
    $pass = 'secret_pass';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // Capture data from the Task C form inputs
        $reg_num = $_POST['reg_number'];
        $mobile  = $_POST['mobile_number'];
        $email   = $_POST['email'];
        $address = $_POST['address'];

        // Execute the distributed data tier write operation
        $stmt = $pdo->prepare("INSERT INTO contacts (registration_number, mobile_number, email, address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$reg_num, $mobile, $email, $address]);

        $message = "Contact registration saved successfully to the database service!";
    } catch (\PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "Error: This registration number already exists in the system.";
        } else {
            $error = "Database Write Failure: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Distributed System Demo - Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f9; padding: 40px; margin: 0; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); margin: 0 auto; }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #666; font-weight: bold; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        textarea { height: 80px; resize: vertical; }
        .btn { padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-right: 10px; text-decoration: none; display: inline-block; }
        .btn-primary { background-color: #28a745; color: white; }
        .btn-primary:hover { background-color: #218838; }
        .btn-secondary { background-color: #0056b3; color: white; }
        .btn-secondary:hover { background-color: #004085; }
        .btn-danger { background-color: #dc3545; color: white; float: right; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 15px; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .nav-links { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-links">
        <span>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</span>
        <a href="logout.php" class="btn btn-danger" style="padding: 5px 10px; font-size: 14px;">Logout</a>
    </div>

    <h2>Task C: Register New Contact Detail</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="dashboard.php" method="POST">
        <div class="form-group">
            <label>iv) Registration Number</label>
            <input type="text" name="reg_number" placeholder="e.g., COMP/123/2026" required>
        </div>
        <div class="form-group">
            <label>i) Mobile Phone Number</label>
            <input type="text" name="mobile_number" placeholder="e.g., +254712345678" required>
        </div>
        <div class="form-group">
            <label>ii) Email Address</label>
            <input type="email" name="email" placeholder="user@domain.com" required>
        </div>
        <div class="form-group">
            <label>iii) Physical Address</label>
            <textarea name="address" placeholder="e.g., Nakuru-Sigor Road, Rongai" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save Contact Data</button>
        <!-- Quick navigation to the upcoming search page feature -->
        <a href="search.php" class="btn btn-secondary">Go to Task D Search</a>
    </form>
</div>

</body>
</html>