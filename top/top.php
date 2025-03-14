<?php
session_start();
require_once('../db_connect.php');

// セッションの名前を保存
$firstName = $_SESSION['first_name'];
$lastName = $_SESSION['last_name'];
$userId = $_SESSION['user_id'];

// ポストに値があったら
if (!empty($_POST)) {
  $post = filter_input(INPUT_POST, 'post', FILTER_SANITIZE_SPECIAL_CHARS);
  $stmt = $db->prepare('INSERT INTO
                          tweets (user_id,tweet)
                        VALUES (:user_id,:tweet)
                        ');
  $stmt->bindValue(':user_id', $userId);
  $stmt->bindValue(':tweet', $post);
  $stmt->execute();
  header('Location:http://localhost/top/top.php');
  exit();
}

// usersと結合してツイートの取得
$stmt = $db->prepare("SELECT 
                        tweets.id,first_name,last_name,tweet 
                      FROM
                        users
                      INNER JOIN
                        tweets
                      ON users.id = tweets.user_id
                      ORDER BY tweets.id desc");
$stmt->execute();
$row = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($row);
?>




<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/destyle.css@1.0.15/destyle.css" />
  <link rel="stylesheet" href="top.css">
  <title>ホーム</title>
</head>

<body>
  <div class="wrapper">
    <h1><?php echo htmlspecialchars($firstName,ENT_QUOTES) . htmlspecialchars($lastName,ENT_QUOTES); ?></h1>
    <div class="flex">
      <main class="main">
        <form action="" class="form" method="post">
          <textarea name="post" id="post" class="post"></textarea>
          <input type="submit" value="投稿する" class="btn">
        </form>
        <div class="cards">
          <?php foreach ($row as $tweet): ?>
            <div class="card">
              <p class="name">
                <?php echo htmlspecialchars($tweet['first_name'],ENT_QUOTES) . htmlspecialchars($tweet['last_name'],ENT_QUOTES); ?>
              </p>
              <p class="tweet"><?php echo htmlspecialchars($tweet['tweet'],ENT_QUOTES); ?></p>
              <a href="http://localhost/reply/reply.php?tweet_id=<?= $tweet["id"] ?>" class="replyBtn">リプライする</a>
            </div>
          <?php endforeach; ?>
        </div>
      </main>
      <aside class="aside">
        <li class="aside__item"><a href="top.php">ホーム</a></li>
        <li class="aside__item"><a href="profile.php">プロフィール</a></li>
      </aside>
    </div>
  </div>
</body>

</html>