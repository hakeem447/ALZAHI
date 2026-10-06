<?php

session_start();

require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if ($username === "" || $password === "") {

        $error = "Please enter username and password.";

    } else {

        $sql = "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin["password"])) {

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Incorrect username or password.";

            }

        } else {

            $error = "Incorrect username or password.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Fresh Harvest</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #0b3d2e,
                    #176b4c,
                    #e9f5ef
                );
        }

        .login-box {
            width: 400px;
            max-width: 92%;
            background: #ffffff;
            padding: 40px;
            border-radius: 22px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.18);
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 18px;
            border-radius: 18px;
            background: #1f9d68;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }

        h1 {
            text-align: center;
            color: #123b2d;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #1f9d68;
        }

        button {
            width: 100%;
            border: none;
            padding: 15px;
            border-radius: 10px;
            background: #1f9d68;
            color: white;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: #167b51;
        }

        .error {
            background: #fff0f0;
            color: #c62828;
            padding: 12px;
            border-radius: 9px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

    </style>

</head>

<body>

    <div class="login-box">

        <div class="logo">
            FH
        </div>

        <h1>Admin Login</h1>

        <p class="subtitle">
            Fresh Harvest Management
        </p>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <label for="username">
                Username
            </label>

            <input
                type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required
            >

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                required
            >

            <button type="submit">
                Sign In
            </button>

        </form>

    </div>

</body>

</html>