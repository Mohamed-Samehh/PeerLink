<?php
    session_start();

    if (!isset($_SESSION['userID'])) {
        echo "<script>alert('Please login first'); window.location='Login.php';</script>";
        exit();
    }

    $loggedInUserId = $_SESSION['userID'];

    $connect = mysqli_connect("localhost", "root", "", "peerlink");
    if (!$connect) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $viewUserId = $_GET['userId'] ?? null;
    
    $query = "SELECT username, name, bio, profilePicture FROM user WHERE id=?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $viewUserId);
    mysqli_stmt_execute($stmt);
    $userData = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($userData);
    mysqli_stmt_close($stmt);

    if (!$user) {
        echo "<script>alert('User not found'); window.location='search_result.php';</script>";
        exit();
    }

    if (isset($_POST['like']) || isset($_POST['unlike'])) {
        $postId = $_POST['postId'];

        if (isset($_POST['like'])) {
            $query = "INSERT INTO likes (userId, postId) VALUES (?, ?)";
        } else {
            $query = "DELETE FROM likes WHERE userId = ? AND postId = ?";
        }
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "ii", $loggedInUserId, $postId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = $_POST['action'];
        $followedId = $viewUserId;

        if ($action === 'follow') {
            $insertQuery = "INSERT INTO follows (followerId, followedId) VALUES (?, ?)";
            $stmt = mysqli_prepare($connect, $insertQuery);
            mysqli_stmt_bind_param($stmt, "ii", $loggedInUserId, $followedId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo json_encode(['status' => 'followed']);
        } elseif ($action === 'unfollow') {
            $deleteQuery = "DELETE FROM follows WHERE followerId = ? AND followedId = ?";
            $stmt = mysqli_prepare($connect, $deleteQuery);
            mysqli_stmt_bind_param($stmt, "ii", $loggedInUserId, $followedId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo json_encode(['status' => 'unfollowed']);
        }
        exit();
    }

    $query = "SELECT username, name, bio, profilePicture FROM user WHERE id=?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "i", $viewUserId);
    mysqli_stmt_execute($stmt);
    $userData = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($userData);
    mysqli_stmt_close($stmt);

    $query = "SELECT p.id, p.content, p.image, p.created_at AS created_at,
            (SELECT COUNT(*) FROM likes l WHERE l.postId = p.id) AS likeCount,
            (SELECT COUNT(*) FROM likes l WHERE l.userId = ? AND l.postId = p.id) AS userLiked
            FROM posts p WHERE p.userId=? ORDER BY p.id DESC";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ii", $loggedInUserId, $viewUserId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    $query = "SELECT * FROM follows WHERE followerId = ? AND followedId = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ii", $loggedInUserId, $viewUserId);
    mysqli_stmt_execute($stmt);
    $isFollowedResult = mysqli_stmt_get_result($stmt);
    $isFollowed = mysqli_num_rows($isFollowedResult) > 0;
    mysqli_stmt_close($stmt);

    $followingQuery = "SELECT COUNT(*) as followingCount FROM follows WHERE followerId = ?";
    $stmt = mysqli_prepare($connect, $followingQuery);
    mysqli_stmt_bind_param($stmt, "i", $viewUserId);
    mysqli_stmt_execute($stmt);
    $followingResult = mysqli_stmt_get_result($stmt);
    $followingCount = mysqli_fetch_assoc($followingResult)['followingCount'];
    mysqli_stmt_close($stmt);

    $followersQuery = "SELECT COUNT(*) as followersCount FROM follows WHERE followedId = ?";
    $stmt = mysqli_prepare($connect, $followersQuery);
    mysqli_stmt_bind_param($stmt, "i", $viewUserId);
    mysqli_stmt_execute($stmt);
    $followersResult = mysqli_stmt_get_result($stmt);
    $followersCount = mysqli_fetch_assoc($followersResult)['followersCount'];
    mysqli_stmt_close($stmt);

    $query = "SELECT * FROM follows WHERE followerId = ? AND followedId = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "ii", $viewUserId, $loggedInUserId);
    mysqli_stmt_execute($stmt);
    $isFollowingMeResult = mysqli_stmt_get_result($stmt);
    $isFollowingMe = mysqli_num_rows($isFollowingMeResult) > 0;
    mysqli_stmt_close($stmt);


    $postCountQuery = "SELECT COUNT(*) as postCount FROM posts WHERE userId = ?";
    $stmt = mysqli_prepare($connect, $postCountQuery);
    mysqli_stmt_bind_param($stmt, "i", $viewUserId);
    mysqli_stmt_execute($stmt);
    $postCountResult = mysqli_stmt_get_result($stmt);
    $postCount = mysqli_fetch_assoc($postCountResult)['postCount'];
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
    
        if (isset($_POST['getFollowers'])) {
            $query = "SELECT u.username, u.profilePicture FROM user u JOIN follows f ON u.id = f.followerId WHERE f.followedId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "i", $viewUserId);
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
                echo '<p>No followers</p>';
            }
            mysqli_stmt_close($stmt);
            exit();
        }
    
        if (isset($_POST['getFollowing'])) {
            $query = "SELECT u.username, u.profilePicture FROM user u JOIN follows f ON u.id = f.followedId WHERE f.followerId = ?";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "i", $viewUserId);
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
                echo '<p>Not following anyone</p>';
            }
            mysqli_stmt_close($stmt);
            exit();
        }
    }    

    mysqli_close($connect);
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

        .follow-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #0077b6;
            color: #ffffff;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 20px;
            margin-top: 10px;
        }

        .follow-btn:hover {
            background-color: #023e8a;
        }

        .post-date {
            font-size: 12px;
            color: #555;
            text-align: right;
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
            $(".like-button").click(function() {
                var button = $(this);
                var postId = button.data('postid');
                var action = button.hasClass('filled') ? 'unlike' : 'like';

                $.ajax({
                    type: "POST",
                    url: "userPosts.php?userId=<?= $viewUserId ?>",
                    data: { postId: postId, [action]: true },
                    success: function(response) {
                        var likeCount = button.siblings(".like-count");
                        var newCount = parseInt(likeCount.text()) + (action === 'like' ? 1 : -1);
                        likeCount.text(newCount);
                        button.toggleClass('filled');
                    }
                });
            });

            $(".follow-btn").click(function() {
                var button = $(this);
                var action = button.hasClass('unfollow') ? 'unfollow' : 'follow';

                $.ajax({
                    type: "POST",
                    url: "userPosts.php?userId=<?= $viewUserId ?>",
                    data: { action: action },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'followed') {
                            button.removeClass('follow').addClass('unfollow').text('Unfollow');
                        } else if (res.status === 'unfollowed') {
                            button.removeClass('unfollow').addClass('follow').text('Follow');
                            if (<?= json_encode($isFollowingMe) ?>) {
                                button.text('Follow Back');
                            }
                        }

                        var followersCountElement = $(".user-stats .followers-count");
                        var newFollowersCount = parseInt(followersCountElement.text()) + (action === 'follow' ? 1 : -1);
                        followersCountElement.text(newFollowersCount);
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".user-stats div:nth-child(1)").click(function() {
                $.ajax({
                    type: "POST",
                    url: "userPosts.php?userId=<?= $viewUserId ?>",
                    data: { getFollowers: true },
                    success: function(response) {
                        $("#followers-panel-content").html(response);
                        togglePanel("#followers-panel");
                        closePanel("#following-panel");
                    }
                });
            });

            $(".user-stats div:nth-child(3)").click(function() {
                $.ajax({
                    type: "POST",
                    url: "userPosts.php?userId=<?= $viewUserId ?>",
                    data: { getFollowing: true },
                    success: function(response) {
                        $("#following-panel-content").html(response);
                        togglePanel("#following-panel");
                        closePanel("#followers-panel");
                    }
                });
            });

            $(".like-count").click(function() {
                var postId = $(this).siblings(".like-button").data('postid');
                $.ajax({
                    type: "POST",
                    url: "userPosts.php?userId=<?= $viewUserId ?>",
                    data: { postId: postId, getLikes: true },
                    success: function(response) {
                        $("#likes-panel-content").html(response);
                        togglePanel("#likes-panel");
                    }
                });
            });

            $(".close-panel").click(function() {
                $(this).closest(".user-panel").fadeOut(200);
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('.user-stats div').length &&
                    !$(e.target).closest('.user-panel').length) {
                    closePanel("#followers-panel");
                    closePanel("#following-panel");
                    closePanel("#likes-panel");
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

    <div class="section__container">
        <img src="data:image/jpeg;base64,<?= base64_encode($user['profilePicture']) ?>" alt="Profile Picture" class="profile-picture">
        <div class="user-details">
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p>@<?= htmlspecialchars($user['username']) ?></p>
            <p><?= htmlspecialchars($user['bio']) ?></p>

            <div class="user-stats">
                <div>
                    <h2>Followers</h2>
                    <p class="followers-count"><?= $followersCount ?></p>
                </div>

                <div>
                    <h2>Posts</h2>
                    <p class="posts-count"><?= $postCount ?></p>
                </div>

                <div>
                    <h2>Following</h2>
                    <p class="following-count"><?= $followingCount ?></p>
                </div>
            </div>

            <button class="follow-btn <?= $isFollowed ? 'unfollow' : ($isFollowingMe ? 'follow-back' : 'follow') ?>">
                <?= $isFollowed ? 'Unfollow' : ($isFollowingMe ? 'Follow Back' : 'Follow') ?>
            </button>
        </div>

        <?php if (count($posts) === 0): ?>
            <p style="font-size: 22px;">No Posts Yet</p><i class="fa fa-camera" style="font-size: 18px;"></i>
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
                            <i class="fa fa-heart like-button <?= $post['userLiked'] ? 'filled' : '' ?>" data-postid="<?= $post['id'] ?>"></i>
                            <span class="like-count"><?= $post['likeCount'] ?></span>
                        </div>
                        <div class="post-date">
                            <?= $post['created_at'] ? date('h:i A', strtotime($post['created_at'])) . '<br>' . date('M d, Y', strtotime($post['created_at'])) : '' ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="followers-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Followers</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content" id="followers-panel-content">
        </div>
    </div>

    <div id="following-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Following</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content" id="following-panel-content">
        </div>
    </div>

    <div id="likes-panel" class="user-panel" style="display: none;">
        <div class="panel-header">
            <h2>Likes</h2>
            <button class="close-panel"><i class="fa-solid fa-x"></i></button>
        </div>
        <div class="panel-content" id="likes-panel-content">
        </div>
    </div>

</body>
</html>
