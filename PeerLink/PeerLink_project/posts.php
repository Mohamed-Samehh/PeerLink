<?php
    session_start();

    if (!isset($_SESSION['userID'])) {
        echo "<script>alert('Please login first'); window.location='Login.php';</script>";
        exit();
    }

    $userID = $_SESSION['userID'];

    $connect = mysqli_connect("localhost", "root", "", "peerlink");
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['like'])) {
            $postId = $_POST['postId'];
            $query = "INSERT INTO likes (userId, postId) VALUES (?, ?)";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "ii", $userID, $postId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo 'liked';
            exit();
        }

        if (isset($_POST['unlike'])) {
            $postId = $_POST['postId'];
            $query = "DELETE FROM likes WHERE userId = ? AND postId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "ii", $userID, $postId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo 'unliked';
            exit();
        }

        if (isset($_POST['delete'])) {
            $postId = $_POST['postId'];
            $query = "DELETE FROM posts WHERE id = ? AND userId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "ii", $postId, $userID);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo 'deleted';
            exit();
        }
    }

    $query = "SELECT username, name, bio, profilePicture FROM user WHERE id=?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $userData = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($userData);
    mysqli_stmt_close($stmt);

    $query = "SELECT p.id, p.content, p.image, p.created_at,
            (SELECT COUNT(*) FROM likes l WHERE l.postId = p.id) AS likeCount,
            (SELECT COUNT(*) FROM likes l WHERE l.userId = ? AND l.postId = p.id) AS userLiked
            FROM posts p WHERE p.userId=? ORDER BY p.id DESC";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ii", $userID, $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    $followingQuery = "SELECT COUNT(*) as followingCount FROM follows WHERE followerId = ?";
    $stmt = mysqli_prepare($connect, $followingQuery);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $followingResult = mysqli_stmt_get_result($stmt);
    $followingCount = mysqli_fetch_assoc($followingResult)['followingCount'];
    mysqli_stmt_close($stmt);

    $followersQuery = "SELECT COUNT(*) as followersCount FROM follows WHERE followedId = ?";
    $stmt = mysqli_prepare($connect, $followersQuery);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $followersResult = mysqli_stmt_get_result($stmt);
    $followersCount = mysqli_fetch_assoc($followersResult)['followersCount'];
    mysqli_stmt_close($stmt);

    $postsCountQuery = "SELECT COUNT(*) as postsCount FROM posts WHERE userId = ?";
    $stmt = mysqli_prepare($connect, $postsCountQuery);
    mysqli_stmt_bind_param($stmt, "i", $userID);
    mysqli_stmt_execute($stmt);
    $postsCountResult = mysqli_stmt_get_result($stmt);
    $postsCount = mysqli_fetch_assoc($postsCountResult)['postsCount'];
    mysqli_stmt_close($stmt);


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['getLikes'])) {
            $postId = $_POST['postId'];
            $query = "SELECT u.username, u.profilePicture FROM user u JOIN likes l ON u.id = l.userId WHERE l.postId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "i", $postId);
            mysqli_stmt_execute($stmt);
            $likesData = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($likesData) > 0) {
                while ($like = mysqli_fetch_assoc($likesData)) {
                    echo '<div class="panel-item">';
                    echo '<img class="panel-picture" src="data:image/jpeg;base64,' . base64_encode($like['profilePicture']) . '" alt="Profile Picture">';
                    echo '<p>@' . htmlspecialchars($like['username']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No likes</p>';
            }
            mysqli_stmt_close($stmt);
            exit();
        }
    }    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerLink - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            background-color: #f4f7f6;
            padding: 0;
            margin: 0;
        }

        header {
            background-color: #ffffff;
            height: 80px;
            display: flex;
            align-items: center;
            border-bottom: solid #aaa 1px;
            padding: 0 20px;
            margin: 0;
            position: sticky;
            top: 0;
            width: 100%;
            z-index: 1000;
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
        }

        .nav__logo a {
            text-decoration: none;
            color: black;
        }

        .links {
            display: flex;
            gap: 2rem;
        }

        .links a, .links button {
            font-weight: 500;
            text-decoration: none;
            color: #0077b6;
            background: none;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .links a:hover, .links button:hover {
            color: #023e8a;
        }

        .section__container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 30px;
        }

        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .user-details {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-details h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .user-details p {
            margin-bottom: 5px;
        }

        .post-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .post-content img {
            width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }

        .like-button {
            cursor: pointer;
            color: black;
        }

        .like-button.filled {
            color: red;
        }

        .trash-button {
            cursor: pointer;
            color: black;
        }

        .trash-button:hover {
            color: red;
        }

        .post-date {
            position: absolute;
            text-align: right;
            top: 10px;
            right: 10px;
            font-size: 12px;
            color: #555;
        }

        .user-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }

        .user-stats div {
            text-align: center;
        }

        .user-stats h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .user-stats p {
            font-size: 16px;
            font-weight: bold;
        }

        .user-stats div {
            text-align: center;
            margin: 0 11px;
        }

        .user-panel {
            position: fixed;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            overflow: hidden;
        }


        .panel-header {
            background-color: #0077b6;
            color: white;
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .panel-header h2 {
            margin: 0;
            font-size: 18px;
        }

        .panel-header .close-panel {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 18px;
        }

        .panel-content {
            padding: 10px;
            max-height: 300px;
            overflow-y: auto;
        }

        .panel-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .panel-item p {
            margin: 0 0 0 10px;
            font-size: 14px;
        }

        .panel-picture {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-stats div {
            text-align: center;
            margin: 0 11px;
            cursor: pointer;
            transition: color 0.3s;
        }

        .like-count {
            cursor: pointer;
            transition: color 0.3s;
        }

        .user-stats div:hover,
        .user-stats div:focus,
        .like-count:hover {
            color: #023e8a;
            text-decoration: none;
        }

        .user-stats div:nth-child(2) {
            text-decoration: none;
            cursor: default;
            color: inherit;
        }

        .user-stats div:nth-child(2):hover {
            color: inherit;
        }
    </style>
    
    <script>
        $(document).ready(function() {
            $(".user-stats div:nth-child(1)").click(function() {
                togglePanel("#followers-panel");
                closePanel("#following-panel");
            });

            $(".user-stats div:nth-child(3)").click(function() {
                togglePanel("#following-panel");
                closePanel("#followers-panel");
            });

            $(".close-panel").click(function() {
                $(this).closest(".user-panel").fadeOut(200);
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('.user-stats div').length &&
                    !$(e.target).closest('.user-panel').length) {
                    closePanel("#followers-panel");
                    closePanel("#following-panel");
                }
            });
        });

        function togglePanel(panelId) {
            $(panelId).fadeToggle(300);
        }

        function closePanel(panelId) {
            $(panelId).fadeOut(200);
        }
    </script>

    <script>
        $(document).ready(function() {
            $(".like-count").click(function() {
                var postId = $(this).siblings(".like-button").data('postid');
                $.ajax({
                    type: "POST",
                    url: "posts.php",
                    data: { postId: postId, getLikes: true },
                    success: function(response) {
                        $("#likes-panel-content").html(response);
                        togglePanel("#likes-panel");
                    }
                });
            });

            $(".close-panel").click(function() {
                $(this).closest(".user-panel").fadeOut();
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('.like-count').length &&
                    !$(e.target).closest('.user-panel').length) {
                    closePanel("#likes-panel");
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $(".like-button").click(function() {
                var button = $(this);
                var postId = button.data('postid');
                var action = button.hasClass('filled') ? 'unlike' : 'like';

                $.ajax({
                    type: "POST",
                    url: "posts.php",
                    data: { postId: postId, [action]: true },
                    success: function(response) {
                        if (action === 'like') {
                            button.addClass('filled');
                            var likeCount = button.siblings(".like-count");
                            var newCount = parseInt(likeCount.text()) + 1;
                            likeCount.text(newCount);
                        } else {
                            button.removeClass('filled');
                            var likeCount = button.siblings(".like-count");
                            var newCount = parseInt(likeCount.text()) - 1;
                            likeCount.text(newCount);
                        }
                    }
                });
            });

            $(".trash-button").click(function() {
                if (confirm("Are you sure you want to delete this post?")) {
                    var button = $(this);
                    var postId = button.data('postid');

                    $.ajax({
                        type: "POST",
                        url: "posts.php",
                        data: { postId: postId, delete: true },
                        success: function(response) {
                            if (response === 'deleted') {
                                button.closest(".post-container").remove();
                            } else {
                                alert("Failed to delete post.");
                            }
                        }
                    });
                }
            });
        });
    </script>
</head>
<body>
    <header>
        <nav>
            <div class="nav__logo">
                <a href="home.php">PeerLink</a>
            </div>
            <div class="links">
                <a href="home.php">Home</a>
                <a href="search_result.php">Connect</a>
                <a href="posts.php">Profile</a>
                <a href="account.php">Settings</a>
            </div>
        </nav>
    </header>
    <section class="section__container">
        <?php if ($user): ?>
            <img class="profile-picture" src="data:image/jpeg;base64,<?= base64_encode($user['profilePicture']) ?>" alt="Profile Picture">
            <div class="user-details">
                <h1><?= htmlspecialchars($user['name']) ?></h1>
                <p>@<?= htmlspecialchars($user['username']) ?></p>
                <p><?= htmlspecialchars($user['bio']) ?></p>
                <div class="user-stats">
                    <div>
                        <h2>Followers</h2>
                        <p><?= $followersCount ?></p>
                    </div>

                    <div>
                        <h2>Posts</h2>
                        <p><?= $postsCount ?></p>
                    </div>

                    <div>
                        <h2>Following</h2>
                        <p><?= $followingCount ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($posts)): ?>
            <p style="font-size: 22px; margin-top: 10px;">No Posts Yet</p><i class="fa fa-camera" style="font-size: 18px;"></i>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-container">
                    <div class="post-content">
                        <p><?= htmlspecialchars($post['content']) ?></p>
                        <?php if ($post['image']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($post['image']) ?>" alt="Post Image">
                        <?php endif; ?>
                    </div>
                    <div class="post-footer">
                        <div>
                            <i class="fas fa-heart like-button <?= $post['userLiked'] ? 'filled' : '' ?>" data-postid="<?= $post['id'] ?>"></i>
                            <span class="like-count"><?= $post['likeCount'] ?></span>
                        </div>
                        <i class="fas fa-trash trash-button" data-postid="<?= $post['id'] ?>"></i>
                        <div class="post-date">
                            <?= $post['created_at'] ? date('h:i A', strtotime($post['created_at'])) . '<br>' . date('M d, Y', strtotime($post['created_at'])) : '' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <div id="likes-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Likes</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content" id="likes-panel-content">
        </div>
    </div>

    <div id="followers-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Followers</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content">
            <?php
            $query = "SELECT u.username, u.profilePicture FROM user u JOIN follows f ON u.id = f.followerId WHERE f.followedId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "i", $userID);
            mysqli_stmt_execute($stmt);
            $followersData = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($followersData) > 0) {
                while ($follower = mysqli_fetch_assoc($followersData)) {
                    echo '<div class="panel-item">';
                    echo '<img class="panel-picture" src="data:image/jpeg;base64,' . base64_encode($follower['profilePicture']) . '" alt="Profile Picture">';
                    echo '<p>@' . htmlspecialchars($follower['username']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>No one has followed you yet</p>';
            }
            mysqli_stmt_close($stmt);
            ?>
        </div>
    </div>

    <div id="following-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Following</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content">
            <?php
            $query = "SELECT u.username, u.profilePicture FROM user u JOIN follows f ON u.id = f.followedId WHERE f.followerId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "i", $userID);
            mysqli_stmt_execute($stmt);
            $followingData = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($followingData) > 0) {
                while ($following = mysqli_fetch_assoc($followingData)) {
                    echo '<div class="panel-item">';
                    echo '<img class="panel-picture" src="data:image/jpeg;base64,' . base64_encode($following['profilePicture']) . '" alt="Profile Picture">';
                    echo '<p>@' . htmlspecialchars($following['username']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo "<p>You haven't followed anyone yet</p>";
            }
            mysqli_stmt_close($stmt);
            ?>
        </div>
    </div>
</body>
</html>
