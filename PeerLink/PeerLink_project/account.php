<?php
    session_start();

    function logout() {
        session_unset();
        session_destroy();
        header("Location: Login.php");
        exit();
    }
    
    if (isset($_POST['logout'])) {
        logout();
    }

    $connect = mysqli_connect("localhost", "root", "", "peerlink");
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if (!isset($_SESSION['userID'])) {
        echo "<script>alert('Please login first'); window.location.href = 'Login.php';</script>";
        exit();
    }

    $userID = $_SESSION['userID'];

    $userData = mysqli_query($connect, "SELECT username, name, email, gender, DOB, phoneNum, bio, profilePicture FROM user WHERE id='$userID'");
    $user = mysqli_fetch_assoc($userData);

    function isUsernameDuplicate($username, $connect, $userID) {
        $query = "SELECT id FROM user WHERE username=? AND id != ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, 'si', $username, $userID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $num_rows = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        return $num_rows > 0;
    }
    
    function updateUser($fields, $connect, $userID) {
        $updateParts = [];
        $params = [];
        $paramTypes = '';
        foreach ($fields as $dbField => $postField) {
            if (isset($_POST[$postField]) && !empty($_POST[$postField])) {
                $updateParts[] = "$dbField=?";
                $params[] = $_POST[$postField];
                $paramTypes .= 's';
            }
        }
        if (!empty($updateParts)) {
            $params[] = $userID;
            $paramTypes .= 'i';
            $stmt = mysqli_prepare($connect, "UPDATE user SET " . implode(", ", $updateParts) . " WHERE id=?");
            mysqli_stmt_bind_param($stmt, $paramTypes, ...$params);
            mysqli_stmt_execute($stmt);
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                echo "<script>alert('Your information has been updated successfully.'); window.location.href = window.location.href;</script>";
            } else {
                echo "<script>alert('No changes were made'); window.location.href = window.location.href;</script>";
            }
            mysqli_stmt_close($stmt);
        }
    }

    function updateUser2() {
        global $connect, $userID, $user;
        
        if ($_FILES['profilePicture']['error'] === UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['profilePicture']['tmp_name']);
            $profilePicture = $imgData;
        } else {
            // Use the original profile picture from the database if no new file is uploaded
            $profilePicture = $user['profilePicture'];
        }
    
        $username = $_POST["username"];
        $name = $_POST["name"];
        $email = $_POST["email"];

        $query = "SELECT id FROM user WHERE username=? AND id != ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, 'si', $username, $userID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $num_rows = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);

        if($num_rows > 0){
            echo "<script>alert('Username is already taken. Please choose another.'); window.location.href = window.location.href;</script>";
            exit();
        }
    
        $sql = "UPDATE user SET profilePicture = ?, username = ?, name = ?, email = ? WHERE id = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssi", $profilePicture, $username, $name, $email, $userID);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            echo "<script>alert('Your information has been updated successfully.'); window.location.href = window.location.href;</script>";
        } else {
            echo "<script>alert('No changes were made'); window.location.href = window.location.href;</script>";
        }
        $stmt->close();
    }
        
    
    if (isset($_POST['generalbtn'])) {
        updateUser2();
    }

    if (isset($_POST['passbtn'])) {
        updateUser(["password" => "newPassword"], $connect, $userID);
    }

    if (isset($_POST['infobtn'])) {
        updateUser([
            "DOB" => "dob",
            "gender" => "gender",
            "phoneNum" => "phone",
            "bio" => "bio"
        ], $connect, $userID);
    }

    mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/css/bootstrap.min.css" rel="stylesheet" async>
    <link rel="stylesheet" href="./includes/styles.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var generalForm = document.getElementById('generalForm');
            var passForm = document.getElementById('passForm');
            var infoForm = document.getElementById('infoForm');
            var msg1 = document.getElementById('msg1');
            var msg2 = document.getElementById('msg2');
            var msg3 = document.getElementById('msg3');
            var isGeneralValid = false;
            var isPassValid = false;
            var isInfoValid = false;
            

            generalForm.addEventListener('submit', function(event) {
                var username = document.getElementById("username").value;
                var name = document.getElementById("name").value;
                var email = document.getElementById("email").value;
                
                
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (username == '') {
                    event.preventDefault();
                    msg1.textContent = "Username can't be empty!";
                    msg2.textContent = "";
                    msg3.textContent = "";
                    msg1.style.fontSize = '22px';
                    msg1.style.color = 'white';
                    msg1.style.paddingTop = '25px';
                    msg1.style.paddingBottom = '10px';
                    msg1.style.textAlign = 'center';
                }

                else if (name == '') {
                    event.preventDefault();
                    msg1.textContent = "Name can't be empty";
                    msg2.textContent = "";
                    msg3.textContent = "";
                    msg1.style.fontSize = '22px';
                    msg1.style.color = 'white';
                    msg1.style.paddingTop = '25px';
                    msg1.style.paddingBottom = '10px';
                    msg1.style.textAlign = 'center';
                }

                else if (!emailPattern.test(email) || email== '') {
                    event.preventDefault();
                    msg1.textContent = "Please enter a valid email address";
                    msg2.textContent = "";
                    msg3.textContent = "";
                    msg1.style.fontSize = '22px';
                    msg1.style.color = 'white';
                    msg1.style.paddingTop = '25px';
                    msg1.style.paddingBottom = '10px';
                    msg1.style.textAlign = 'center';
                }
            });



            passForm.addEventListener('submit', function(event) {
                var newPassword = document.getElementById("newPassword").value;
                var repeatPassword = document.getElementById("rePassword").value;
                

                if (newPassword.length < 5) {
                    event.preventDefault();
                    msg2.textContent = "New password should be at least 5 characters long";
                    msg1.textContent = "";
                    msg3.textContent = "";
                    msg2.style.fontSize = '22px';
                    msg2.style.color = 'white';
                    msg2.style.paddingTop = '25px';
                    msg2.style.paddingBottom = '10px';
                    msg2.style.textAlign = 'center';
                }

                else if (newPassword != repeatPassword) {
                    event.preventDefault();
                    msg2.textContent = "New passwords do not match";
                    msg1.textContent = "";
                    msg3.textContent = "";
                    msg2.style.fontSize = '22px';
                    msg2.style.color = 'white';
                    msg2.style.paddingTop = '25px';
                    msg2.style.paddingBottom = '10px';
                    msg2.style.textAlign = 'center';
                }
            });



            infoForm.addEventListener('submit', function(event) {
                var dob = document.getElementById("dob").value;
                var gender = document.getElementById("gender").value;
                var phone = document.getElementById("phone").value;
                var bio = document.getElementById("bio").value;
                

                if (dob === '') {
                    event.preventDefault();
                    msg3.textContent = "Date of birth can't be empty";
                    msg1.textContent = "";
                    msg2.textContent = "";
                    msg3.style.fontSize = '22px';
                    msg3.style.color = 'white';
                    msg3.style.paddingTop = '25px';
                    msg3.style.paddingBottom = '10px';
                    msg3.style.textAlign = 'center';
                }

                else if (gender === '') {
                    event.preventDefault();
                    msg3.textContent = "Gender can't be empty";
                    msg1.textContent = "";
                    msg2.textContent = "";
                    msg3.style.fontSize = '22px';
                    msg3.style.color = 'white';
                    msg3.style.paddingTop = '25px';
                    msg3.style.paddingBottom = '10px';
                    msg3.style.textAlign = 'center';
                }

                else if (phone === '') {
                    event.preventDefault();
                    msg3.textContent = "Phone number can't be empty";
                    msg1.textContent = "";
                    msg2.textContent = "";
                    msg3.style.fontSize = '22px';
                    msg3.style.color = 'white';
                    msg3.style.paddingTop = '25px';
                    msg3.style.paddingBottom = '10px';
                    msg3.style.textAlign = 'center';
                }

                else if (bio === '') {
                    event.preventDefault();
                    msg3.textContent = "Your Bio can't be empty";
                    msg1.textContent = "";
                    msg2.textContent = "";
                    msg3.style.fontSize = '22px';
                    msg3.style.color = 'white';
                    msg3.style.paddingTop = '25px';
                    msg3.style.paddingBottom = '10px';
                    msg3.style.textAlign = 'center';
                }
                
                else {
                    var today = new Date();
                    var birthDate = new Date(dob);
                    var age = today.getFullYear() - birthDate.getFullYear();
                    var m = today.getMonth() - birthDate.getMonth();
                    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                        age--;
                    }

                    if (age < 18) {
                        event.preventDefault();
                        msg3.textContent = "You must be at least 18 years old";
                        msg1.textContent = "";
                        msg2.textContent = "";
                        msg3.style.fontSize = '22px';
                        msg3.style.color = 'white';
                        msg3.style.paddingTop = '25px';
                        msg3.style.paddingBottom = '10px';
                        msg3.style.textAlign = 'center';
                    }
                }
            });
        });
    </script>

<title>PeerLink - Settings</title>
</head>

<body>
    <nav>
        <div class="nav__logo"><a href="home.php">PeerLink</a></div>
        <ul class="nav__links">
            <li class="links"><a href="home.php">Home</a></li>
            <li class="links"><a href="search_result.php">Connect</a></li>
            <li class="links"><a href="posts.php">Profile</a></li>
            <li class="links"><a href="account.php">Settings</a></li>
            <form method="POST" style="display:inline;">
                <button type="submit" id="logout" name="logout">Logout</button>
            </form>
        </ul>
    </nav>

<body>
    <div class="pic_container">
        <img class="profile-picture" src="data:image/jpeg;base64,<?php echo base64_encode($user['profilePicture']); ?>" alt="Profile Picture">
    </div>
    <br>

    <div id="pd">
    </div>

    <div class="container light-style flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-4">
            Account Details
        </h4>
        <div class="card overflow-hidden">
            <div class="row no-gutters row-bordered row-border-light">
                <div class="col-md-3 pt-0">
                    <div class="list-group list-group-flush account-settings-links">
                        <a class="list-group-item list-group-item-action active" data-toggle="list"
                            href="#account-general">General</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list"
                            href="#account-change-password">Change password</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list"
                            href="#account-info">Info</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="account-general">
                            <hr class="border-light m-0">
                            <div class="card-body">
                            <form id="generalForm" method="post" action="account.php" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" id="profilePicture" name="profilePicture" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" value="<?php echo $user['username']; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" id="name" name="name" class="form-control" value="<?php echo $user['name']; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">E-mail</label>
                                    <input type="text" id="email" name="email" class="form-control" value="<?php echo $user['email']; ?>">
                                </div>
                                <button type="submit" name="generalbtn" class="btn btn-primary">Submit</button>
                                </form>
                                <div id="msg1">
                            </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="account-change-password">
                            <div class="card-body pb-2">
                            <form id="passForm" method="post" action="account.php">
                                <div class="form-group">
                                    <label class="form-label">New password</label>
                                    <input type="password" id="newPassword" name="newPassword" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Repeat new password</label>
                                    <input type="password" id="rePassword" name="rePassword" class="form-control">
                                </div>
                                <button type="submit" name="passbtn" class="btn btn-primary">Submit</button>
                            </form>
                            <div id="msg2">
                            </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="account-info">
                            <div class="card-body pb-2">
                            <form id="infoForm" method="post" action="account.php">
                                <div class="form-group">
                                    <label class="form-label">Birthday</label>
                                    <input type="date" id="dob" name="dob" class="form-control" value="<?php echo $user['DOB']; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <br>
                                    <select id="gender" name="gender" class="form-control">
                                        <option <?php if ($user['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                                        <option <?php if ($user['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $user['phoneNum']; ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Bio</label>
                                    <input type="text" id="bio" name="bio" class="form-control" value="<?php echo $user['bio']; ?>">
                                </div>
                                <button type="submit" name="infobtn" class="btn btn-primary">Submit</button>
                            </form>
                            <div id="msg3">
                            </div>
                            </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="pd2">
        </div>
    </div>
</body>
</html>
