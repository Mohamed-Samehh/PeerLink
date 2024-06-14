<?php
    session_start();

    $connect = mysqli_connect("localhost", "root", "", "peerlink");
    if (!$connect) {
        die("Cannot connect to database");
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if ($_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['img']['tmp_name']);
            $name = $_POST["name"];
            $number = $_POST["number"];
            $DOB = $_POST["DOB"];
            $email = $_POST["email"];
            $sex = $_POST["sex"];
            $username = $_POST["username"];
            $password = $_POST["createpassword"];
            $bio = $_POST["bio"];

            $checkUsernameQuery = "SELECT * FROM user WHERE username = ?";
            $stmt = mysqli_prepare($connect, $checkUsernameQuery);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                echo "<script>alert('Error during registration: Username already exists.');</script>";
            } else {
                $query = "INSERT INTO user (name, phoneNum, DOB, email, gender, username, password, profilePicture, bio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($connect, $query);
                mysqli_stmt_bind_param($stmt, "sssssssss", $name, $number, $DOB, $email, $sex, $username, $password, $imgData, $bio);
                mysqli_stmt_execute($stmt);
                
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    $_SESSION['username'] = $username;
                    $_SESSION['userID'] = mysqli_insert_id($connect);
                    echo "<script>window.location.href='home.php';</script>";
                } else {
                    echo "<script>alert('Error during registration');</script>";
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            echo "<script>alert('Error in file upload');</script>";
        }
        mysqli_close($connect);
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

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 2rem 0;
        }

        #signup-form {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .input-group input,
        .input-group select,
        .input-group textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
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

        #subscription-message {
            text-align: center;
            font-size: 1rem;
            color: red;
        }

        #charCount {
            text-align: right;
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #888;
        }

        #myCheckbox {
            margin-right: 0.5rem;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .info-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 1rem;
            text-align: center;
        }

        #login a {
            color: #007bff;
            text-decoration: none;
        }

        #login a:hover {
            text-decoration: underline;
        }

        #sex{
            width: 104%;
        }
    </style>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            var submitButton = document.getElementById("submitButton");
            var form = document.getElementById('signup-form');
            var subscribeText = document.getElementById('subscription-message');
    
            submitButton.addEventListener('click', function() {
                var nameInput = document.getElementById('name');
                var emailInput = document.getElementById('email');
                var numberInput = document.getElementById('number');
                var userInput = document.getElementById('username');
                var crpassInput = document.getElementById('createpassword');
                var conpassInput = document.getElementById('confirmpassword');
                var DOBInput = document.getElementById('DOB');
                var sexInput = document.getElementById('sex');
                var bioInput = document.getElementById('bio');
                var today = new Date();
                var DOBDate = new Date(DOBInput.value);
    
                if ((nameInput.value !== "" && numberInput.value !== "") && (crpassInput.value !== "" && conpassInput.value !== "") && (crpassInput.value == conpassInput.value) && (userInput.value !== "" && bioInput.value !== "") && (DOBInput.value !== "" && crpassInput.value.length >= 5) && (sexInput.value == "Male" || sexInput.value == "Female") && isValidEmail(emailInput.value)) {
                    var differenceMs = today - DOBDate;
                    var differenceYears = differenceMs / (1000 * 60 * 60 * 24 * 365.25);

                    if(differenceYears >= 18){
                        form.removeEventListener('submit', preventSubmit);
                    } else {
                        form.addEventListener('submit', preventSubmit);
                        subscribeText.textContent = "Error! You have to be 18 or older";
                        subscribeText.style.fontSize = '22px';
                        subscribeText.style.paddingTop = '15px';
                    }
                } else if(crpassInput.value != conpassInput.value){
                    form.addEventListener('submit', preventSubmit);
                    subscribeText.textContent = "Error! Passwords don't match";
                    subscribeText.style.fontSize = '22px';
                    subscribeText.style.paddingTop = '15px';
                } else {
                    if((crpassInput.value.length < 5)){
                        form.addEventListener('submit', preventSubmit);
                        subscribeText.textContent = "Password should be 5 characters or more";
                        subscribeText.style.fontSize = '22px';
                        subscribeText.style.paddingTop = '15px';
                    } else {
                        form.addEventListener('submit', preventSubmit);
                        subscribeText.textContent = "Error! Invalid format";
                        subscribeText.style.fontSize = '22px';
                        subscribeText.style.paddingTop = '15px';
                    }
                }
            });
    
            function preventSubmit(event) {
                event.preventDefault();
            }
    
            function isValidEmail(email) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return emailRegex.test(email);
            }

            document.getElementById('bio').addEventListener('input', function() {
                var maxLength = parseInt(document.getElementById('bio').getAttribute('maxlength'));
                var currentLength = document.getElementById('bio').value.length;
                var remainingLength = maxLength - currentLength;
                document.getElementById('charCount').textContent = 'Characters remaining: ' + remainingLength;
            });
        });

        $(document).ready(function() {
            $('#myCheckbox').click(function() {
                if ($(this).is(':checked')) {
                    submitButton.disabled = false;
                } else {
                    submitButton.disabled = true;
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <form id="signup-form" action="Sign Up.php" method="post" enctype="multipart/form-data">
            <h2>Sign up to PeerLink</h2>
            <div class="input-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Enter your name">
            </div>
            <div class="input-group">
                <label for="number">Phone</label>
                <input type="text" id="number" name="number" placeholder="Enter your phone number">
            </div>
            <div class="input-group">
                <label for="DOB">DOB</label>
                <input type="date" id="DOB" name="DOB">
            </div>
            <div class="input-group">
                <label for="sex">Gender</label>
                <select id="sex" name="sex">
                    <option selected disabled>Select your gender</option>
                    <option>Male</option>
                    <option>Female</option>
                </select>
            </div>
            <div class="input-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Enter your email address">
            </div>
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username">
            </div>
            <div class="input-group">
                <label for="createpassword">Create Password</label>
                <input type="password" id="createpassword" name="createpassword" placeholder="Enter password">
            </div>
            <div class="input-group">
                <label for="confirmpassword">Confirm Password</label>
                <input type="password" id="confirmpassword" name="confirmpassword" placeholder="Re-enter password">
            </div>
            <div class="input-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" placeholder="Tell us something about yourself" maxlength="100"></textarea>
                <p id="charCount">Characters remaining: 100</p>
            </div>
            <div class="input-group">
                <label for="img">Upload Your Picture</label>
                <input type="file" id="img" name="img" required>
            </div>
            <button type="submit" id="submitButton" disabled>Create Account</button>
            <div class="checkbox-group">
                <input type="checkbox" id="myCheckbox">
                <label for="myCheckbox">Agree to Terms & Conditions</label>
            </div>
            <p class="info-text">*You have to be 18 or older</p>
            <p class="info-text">*Password should be 5 characters long or more</p>
            <p class="info-text" id="login">Already have an account? <a href="Login.php">Login Here</a></p>
            <div id="subscription-message"></div>
        </form>
    </div>
</body>
</html>
