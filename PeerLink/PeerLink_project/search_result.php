<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "PeerLink";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['userID'])) {
    echo "<script>alert('Please login first'); window.location.href = 'Login.php';</script>";
    exit();
}

$userID = $_SESSION['userID'];

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search = $conn->real_escape_string($search);

$query = "
    SELECT u.id, u.username, u.name, u.profilePicture, 
           IF(f.followerId IS NOT NULL, 1, 0) AS isFollowed
    FROM user u
    LEFT JOIN follows f ON f.followedId = u.id AND f.followerId = $userID
    WHERE u.username LIKE '$search%' AND u.id != $userID
    ORDER BY isFollowed DESC, u.username ASC
";
$result = $conn->query($query);

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $followedId = $_POST['followedId'];

    $response = [];
    if ($action === 'follow') {
        $insertQuery = "INSERT INTO follows (followerId, followedId) VALUES ($userID, $followedId)";
        if ($conn->query($insertQuery) === TRUE) {
            $response['status'] = 'followed';
        } else {
            $response['error'] = $conn->error;
        }
    } elseif ($action === 'unfollow') {
        $deleteQuery = "DELETE FROM follows WHERE followerId = $userID AND followedId = $followedId";
        if ($conn->query($deleteQuery) === TRUE) {
            $response['status'] = 'unfollowed';
        } else {
            $response['error'] = $conn->error;
        }
    }
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>PeerLink - Connect</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            font-family: "Poppins", sans-serif;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            overflow-x: hidden;
        }

        header {
            background-color: #ffffff;
            height: 80px;
            display: flex;
            align-items: center;
            border-bottom: solid #aaa 1px;
            padding: 0 20px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            width: 100%;
            align-items: center;
        }

        .nav__logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: black;
        }

        .nav__logo a {
            text-decoration: none;
            color: black;
        }

        .links {
            display: flex;
            gap: 20px;
        }

        .links a {
            font-weight: 500;
            text-decoration: none;
            color: #0077b6;
            transition: 0.3s;
        }

        .links a:hover {
            color: #023e8a;
        }

        .search_result_container {
            padding: 20px 0;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
        }

        .search_bar {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            border-radius: 50px;
            padding: 10px 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        .search_bar i {
            color: black;
            margin-right: 10px;
        }

        .search_bar input {
            font-size: 1rem;
            border: none;
            outline: none;
            padding: 10px;
            width: 100%;
        }

        #root {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 20px;
        }

        .box:hover {
            transform: translateY(-10px);
        }

        .box {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            flex: 0 1 280px;
            box-sizing: border-box;
        }

        .img-box {
            width: 150px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 50%;
            margin-bottom: 20px;
        }

        .images {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .bottom {
            text-align: center;
        }

        .user-link {
            text-decoration: none;
            color: black;
        }

        .user-link .user-name,
        .user-link .user-username {
            margin: 10px 0;
            font-weight: 600;
            color: black;
            transition: color 0.3s;
        }

        .user-link .user-name:hover,
        .user-link .user-username:hover {
            color: #0077b6;
        }

        .bottom button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #0077b6;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .bottom button:hover {
            background-color: #023e8a;
        }

        #no_users {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 50vh;
        }

        .no-users {
            text-align: center;
            margin: 20px;
        }

        @media (max-width: 1200px) {
            .box {
                flex: 0 1 30%;
            }
        }

        @media (max-width: 768px) {
            .box {
                flex: 0 1 45%;
            }
        }

        @media (max-width: 480px) {
            .box {
                flex: 0 1 90%;
            }
        }
    </style>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.follow-btn', function() {
                var $button = $(this);
                var followedId = $button.data('followed-id');
                var action = $button.data('action');

                $.ajax({
                    url: 'search_result.php',
                    type: 'POST',
                    data: {
                        action: action,
                        followedId: followedId
                    },
                    success: function(response) {
                        console.log(response);
                        if (response.status === 'followed') {
                            $button.text('Unfollow');
                            $button.data('action', 'unfollow');
                        } else if (response.status === 'unfollowed') {
                            $button.text('Follow');
                            $button.data('action', 'follow');
                        } else if (response.error) {
                            alert(response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            });
        });
    </script>

</head>

<body>

    <header>
        <nav>
            <div class="nav__logo"><a href="home.php">PeerLink</a></div>
            <div class="links">
                <a href="home.php">Home</a>
                <a href="search_result.php">Connect</a>
                <a href="posts.php">Profile</a>
                <a href="account.php">Settings</a>
            </div>
        </nav>
    </header>
    <div class="search_result_container">
        <form method="GET">
            <div class="search_bar">
                <i class="fa fa-search" style="font-size:24px"></i>
                <input id="searchBar" name="search" placeholder="Search Username" type="text" value="<?= htmlspecialchars($search) ?>">
            </div>
        </form>
    </div>

    <?php if (empty($users)): ?>
    <div id="no_users">
        <div class='no-users'>
            <p style="font-size: 26px; color: black;">No Users Found</p>
        </div>
    </div>
    <?php else: ?>
        <div id="root">
            <?php foreach ($users as $user): ?>
                <div class='box'>
                    <div class='img-box'>
                        <a href="userPosts.php?userId=<?= $user['id'] ?>">
                            <img class='images' src='data:image/jpeg;base64,<?= base64_encode($user['profilePicture']) ?>' alt='Profile Picture'>
                        </a>
                    </div>
                    <div class='bottom'>
                        <a href="userPosts.php?userId=<?= $user['id'] ?>" class="user-link">
                            <p class="user-name"><?= htmlspecialchars($user['name']) ?></p>
                            <p class="user-username">@<?= htmlspecialchars($user['username']) ?></p>
                        </a>
                        
                        <?php
                            $followedId = $user['id'];
                            $checkQuery = "SELECT * FROM follows WHERE followerId = $userID AND followedId = $followedId";
                            $checkResult = $conn->query($checkQuery);

                            if ($checkResult->num_rows > 0) {
                                echo "<button class='follow-btn' data-followed-id='$followedId' data-action='unfollow'>Unfollow</button>";
                            } else {
                                echo "<button class='follow-btn' data-followed-id='$followedId' data-action='follow'>Follow</button>";
                            }
                        ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; $conn->close(); ?>
</body>

</html>
