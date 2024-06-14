<?php
    session_start();

    if (!isset($_SESSION['userID'])) {
        echo "<script>alert('Please login first'); window.location='Login.php';</script>";
        exit();
    }

    $userID = $_SESSION['userID'];

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

    if (isset($_POST['submit'])) {
        if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $imageData = file_get_contents($_FILES['image']['tmp_name']);
            $content = $_POST['content'];

            $query = "INSERT INTO posts (userId, content, image) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, "iss", $userID, $content, $imageData);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            echo "<script>alert('Post successfully added'); window.location.href = window.location.href;</script>";
            exit();
        } else {
            echo "<script>alert('Error in file upload');</script>";
        }
    }

    if (isset($_POST['like']) || isset($_POST['unlike'])) {
        $postId = $_POST['postId'];

        if (isset($_POST['like'])) {
            $query = "INSERT INTO likes (userId, postId) VALUES (?, ?)";
        } else {
            $query = "DELETE FROM likes WHERE userId = ? AND postId = ?";
        }
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "ii", $userID, $postId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    if (isset($_POST['delete'])) {
        $postId = $_POST['postId'];
        $query = "DELETE FROM posts WHERE id = ? AND userId = ?";
        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "ii", $postId, $userID);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $query = "SELECT p.id, p.userId, p.content, p.image, p.created_at, u.username, u.profilePicture, 
            (SELECT COUNT(*) FROM likes l WHERE l.postId = p.id) AS likeCount,
            (SELECT COUNT(*) FROM likes l WHERE l.userId = ? AND l.postId = p.id) AS userLiked
            FROM posts p
            JOIN user u ON u.id = p.userId
            WHERE p.userId = ? OR p.userId IN (SELECT followedId FROM follows WHERE followerId = ?)
            ORDER BY p.id DESC";

    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, "iii", $userID, $userID, $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['getLikes'])) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PeerLink - Home</title>
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

        .links a,
        .links button {
            font-weight: 500;
            text-decoration: none;
            color: #0077b6;
            background: none;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .links a:hover,
        .links button:hover {
            color: #023e8a;
        }

        .section__container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .header__container {
            margin-bottom: 20px;
            padding-top: 30px;
        }

        .post-button-container {
            margin-bottom: 20px;
        }

        .add-post-btn {
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-post-btn:hover {
            background-color: #023e8a;
        }

        .post-form-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            width: 100%;
            max-width: 600px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .submit-btn {
            padding: 10px 20px;
            background-color: #0077b6;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #023e8a;
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

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .left-content {
            display: flex;
            align-items: center;
        }

        .left-content img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .left-content a {
            text-decoration: none;
            color: inherit;
        }

        .left-content a:hover {
            text-decoration: underline;
        }

        .right-content {
            text-align: right;
        }

        .post-date {
            font-size: 0.9em;
            color: #777;
        }

        .post-content {
            margin-bottom: 10px;
        }

        .post-content p {
            margin: 0;
        }

        .post-content img {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .like-button,
        .trash-button {
            cursor: pointer;
            color: black;
        }

        .like-button.filled {
            color: red;
        }

        .trash-button:hover {
            color: red;
        }

        #follow-message {
            font-size: 20px;
            margin-top: 15px;
            text-align: center;
            padding: 15px;
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

        .like-count {
            cursor: pointer;
            transition: color 0.3s;
        }

        .like-count:hover {
            color: #023e8a;
            text-decoration: none;
        }

        #scrollToTopBtn {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 30px;
            z-index: 99;
            width: 50px;
            height: 50px;
            border: none;
            outline: none;
            background-color: #0077b6;
            color: white;
            cursor: pointer;
            border-radius: 50%;
            font-size: 18px;
            transition: background-color 0.3s;
            text-align: center;
            line-height: 50px;
        }

        #scrollToTopBtn:hover {
            background-color: #023e8a;
        }
    </style>

    <script>
        $(document).ready(function() {
            $(window).scroll(function() {
                if ($(this).scrollTop() > 200) {
                    $('#scrollToTopBtn').fadeIn();
                } else {
                    $('#scrollToTopBtn').fadeOut();
                }
            });

            $('#scrollToTopBtn').click(function() {
                $('html, body').animate({ scrollTop: 0 }, 600);
                return false;
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $(".add-post-btn").click(function() {
                $("#postForm").toggle();
            });

            $(".like-button").click(function() {
                var button = $(this);
                var postId = button.data('postid');
                var action = button.hasClass('filled') ? 'unlike' : 'like';

                $.ajax({
                    type: "POST",
                    url: "home.php",
                    data: { postId: postId, [action]: true },
                    success: function(response) {
                        var likeCount = button.siblings(".like-count");
                        var newCount = parseInt(likeCount.text()) + (action === 'like' ? 1 : -1);
                        likeCount.text(newCount);
                        button.toggleClass('filled');
                    }
                });
            });

            $(".trash-button").click(function() {
                if (confirm("Are you sure you want to delete this post?")) {
                    var button = $(this);
                    var postId = button.data('postid');

                    $.ajax({
                        type: "POST",
                        url: "home.php",
                        data: { postId: postId, delete: true },
                        success: function(response) {
                            button.closest(".post-container").remove();
                        }
                    });
                }
            });
        });
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
                $(this).closest(".user-panel").fadeOut(200);
            });

            $(document).click(function(e) {
                if (!$(e.target).closest('.like-count').length &&
                    !$(e.target).closest('.user-panel').length) {
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
    <section class="section__container">
        <div class="header__container">
            <h1>Welcome to PeerLink</h1>
        </div>
        <div class="post-button-container">
            <button class="add-post-btn">Add Post</button>
        </div>
        <div id="postForm" class="post-form-container" style="display:none;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="content">Caption</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                </div>
                <button type="submit" name="submit" class="submit-btn">Submit</button>
            </form>
        </div>
        <?php if (empty($posts)): ?>
                <p id="follow-message">Start following people to see their latest posts</p>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-container">
                    <div class="post-header">
                        <div class="left-content">
                            <a href="<?= $post['userId'] == $userID ? 'posts.php' : 'userPosts.php?userId=' . $post['userId'] ?>">
                                <img src="data:image/jpeg;base64,<?= base64_encode($post['profilePicture']) ?>" alt="Profile Picture">
                            </a>
                            <a href="<?= $post['userId'] == $userID ? 'posts.php' : 'userPosts.php?userId=' . $post['userId'] ?>">
                                <span><?= htmlspecialchars($post['username']) ?></span>
                            </a>
                        </div>
                        <div class="right-content">
                        <span class="post-date">
                            <?= $post['created_at'] ? date('h:i A', strtotime($post['created_at'])) . '<br>' . date('M d, Y', strtotime($post['created_at'])) : '' ?>
                        </span>
                        </div>
                    </div>
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
                        <?php if ($post['userId'] == $userID): ?>
                            <i class="fas fa-trash trash-button" data-postid="<?= $post['id'] ?>"></i>
                        <?php endif; ?>
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

    <button id="scrollToTopBtn" title="Go to top"><i class="fa fa-arrow-up"></i></button>

</body>
</html>