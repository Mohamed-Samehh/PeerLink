<?php
    session_start();

    $host = 'localhost';
    $dbname = 'peerlink';
    $dbUsername = 'root';
    $dbPassword = '';

    $conn = new mysqli($host, $dbUsername, $dbPassword, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $uname = mysqli_real_escape_string($conn, $_POST['username']);
        $pwd = mysqli_real_escape_string($conn, $_POST['password']);
        $sql = "SELECT id, password FROM user WHERE username='$uname'";

        $result = $conn->query($sql);

        if ($result->num_rows == 0) {
            $error = "Account not found!";
        } else {
            $user = $result->fetch_assoc();
            if ($pwd === $user['password']) {
                $_SESSION['username'] = $uname;
                $_SESSION['userID'] = $user['id'];
                
                echo "<script>window.location='home.php';</script>";
                exit();
            } else {
                $error = "Wrong password!";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

        body {
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: auto;
            font-family: "Poppins", sans-serif;
        }
        
        #login-form {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            margin: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        #submitButton {
            width: 104%;
            padding: 0.75rem;
            border: none;
            border-radius: 5px;
            background-color: #28a745;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #submitButton:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        #submitButton:hover:not(:disabled) {
            background-color: #218838;
        }

        .inputs {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        #subscription-message {
            margin-top: 0.5rem;
            font-size: 20px;
            color: red;
            text-align: center;
        }

        #signup {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.875rem;
        }

        #signup a {
            color: #007bff;
            text-decoration: none;
        }

        #signup a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1></h1>
    <section>
        <form id="login-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <h2>Login to PeerLink</h2>
            <br>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" class="inputs">
            <br>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" class="inputs">
            <br>
            <button type="submit" id="submitButton" value="Submit">Login</button>
            <br>
            <div id="subscription-message"><?php if(isset($error)) echo $error; ?></div>
            <br>
            <div id="signup">First time? <a href="Sign Up.php">Signup here</a></div>
        </form>
    </section>
</body>
</html>
