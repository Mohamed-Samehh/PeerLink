# PeerLink - Social Media Platform

## Overview

PeerLink is a dynamic social media platform designed to connect users through posts, likes, and follows. It provides a space for users to share their thoughts and media and interact with each other, fostering a vibrant online community. PeerLink emphasizes user-friendly design, privacy, and robust features to enhance social interactions and user engagement.

## Features

- **User Authentication**
  - **Secure Login and Registration:** Users can securely register and log in to their accounts.
  - **Session Management:** Sessions are maintained to ensure that users remain logged in securely until they choose to log out.

- **Post Creation and Interaction**
  - **Post Creation:** Users can create text and image posts, sharing updates with their network. The platform supports rich media content to make posts more engaging.
  - **Like and Unlike:** Users can like and unlike posts, with real-time updates on like counts. This feature helps users interact with the content they enjoy.

- **Profile Management**
  - **Profile Customization:** Users can update their profile picture, username, name, bio, email, and other personal details. This feature allows users to express their identity and manage their online presence.
  - **Password Management:** Users can change their passwords securely to maintain account security and privacy.

- **Social Interaction**
  - **Follow and Unfollow:** Users can follow or unfollow each other to personalize their feed with content from users they are interested in.
  - **View Followers and Following:** Users can view lists of who they are following and who follows them, making it easy to manage their social network.

- **Content Discovery**
  - **Search Functionality:** Users can search for other users by their usernames enabling easy discovery of new content and connections.

## Technologies Used

**Frontend**
- **HTML and CSS:** Used for structuring and designing the user interface, ensuring it is responsive and visually appealing.
- **JavaScript and jQuery:** Enhance frontend interactivity, manage user inputs, and handle dynamic content updates.
- **AJAX:** Allows for asynchronous data loading, enabling dynamic updates without reloading pages.

**Backend**
- **PHP:** Handles server-side scripting, manages backend logic, and processes database interactions.
- **MySQL:** A relational database management system used for storing user data, posts, likes, follows, and comments.
- **PhpMyAdmin:** A web interface tool for managing MySQL databases, making it easier to maintain and administer the database.

## Pages

- **Home (`home.php`):** The main feed where users can view and interact with posts from the people they follow.
- **Settings (`account.php`):** Allows users to manage their profiles, update personal information, and change passwords.
- **This User's Profile (`posts.php`):** Displays this user’s posts, followers, and following lists.
- **Other User's Profile (`userPosts.php`):** Displays the other user's posts, followers, and following lists.
- **Login (`Login.php`):** The login page where users can access their accounts securely.
- **Sign Up (`Sign Up.php`):** The registration page where new users can create an account.
- **Connect (`search_result.php`):** This page lists all registered users of the platform. It enables users to search for specific accounts using their usernames.

## Conclusion

PeerLink is an ideal solution for users seeking to engage in a social networking environment that is user-friendly, secure, and feature-rich. With a focus on user interaction, content discovery, and privacy, PeerLink aims to provide a comprehensive social media experience that connects people and encourages community engagement. Whether sharing updates, discovering new connections, or interacting with content, PeerLink offers a robust platform to meet the diverse needs of its users.
