<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Centre Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            /*background: linear-gradient(to bottom left, #0f172a, #1e1a78, #0f172a);*/
             background:
                url('assets/images/hc.jpg') no-repeat center center fixed;
    background-size: cover
        }

        /* Main container simulating a card */
        .container {
            display: flex;
            background: linear-gradient(to bottom left, #0f172a, #1e1a78, #0f172a);
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            width: 80%;
            max-width: 1000px;
            overflow: hidden;
            height: 70%;
        }

        /* Left panel - illustration area */
        .left {
            flex: 1;
            background: transparent;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 20px;
        }

        .left img {
            max-width: 80%;
            height: auto;
            margin-bottom: 20px;
        }

        /* Right panel - login form */
        .right {
            flex: 1;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        /* Login form container */
        .form-container {
            width: 85%;
            max-width: 350px;
            padding: 20px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 1.6rem;
            color: #1e1a78;
        }

        h2 i {
            margin-bottom: 10px;
            font-size: 1.8rem;
            color: #1e1a78;
            display: block;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

       button {
    display: block;
    width: 109%;
    padding: 14px;
    background: #1e1a78;
    border: none;
    color: white;
    font-size: 16px;
    font-weight: 500;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
    margin-top: 10px;
    text-align: center;
}

button:hover {
    background: #1556beff;
}


        .error {
            color: red;
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>

<body>
<?php
require('../config/autoload.php');
$dao = new DataAccess();

$msg = "";

// Login processing
if (isset($_POST['login'])) {
    $email = $_POST['email'] ?? '';
    $pass = $_POST['password'] ?? '';

    // Check user credentials
    $user = $dao->getData("*", "healthcentre", "email='$email' AND password='$pass'");
    if (!empty($user)) {
        $_SESSION['healthcentre'] = $user[0]; // Store full health centre data
        header("Location: preloader.php");
        exit;
    } else {
        $msg = "Invalid Email or Password";
    }
}
?>

<div class="container">
    <!-- Left Panel -->
    <div class="left">
        <img src="assets/images/log1.png" alt="Doctors Illustration" class="logo">
        <h1>Welcome!</h1>
    </div>

    <!-- Right Panel -->
    <div class="right">
        <div class="form-container">
            <h2><i class="fas fa-hospital"></i>Health Centre Login</h2>
            <form method="POST">
                <input type="email" name="email" placeholder="E-mail" autocomplete="off" required>
                <input type="password" name="password" placeholder="Password" autocomplete="off" required>
                <button type="submit" name="login">Login</button>
                <?php if (!empty($msg)): ?>
                    <p class="error"><?= $msg ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
</body>
</html>
