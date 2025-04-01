<?php
session_start();
require_once('../db_connect.php');

// セッションの名前を保存
$session_firstName = $_SESSION['first_name'];
$session_lastName = $_SESSION['last_name'];
$session_userId = $_SESSION['user_id'];

// ログインしていなければログイン画面にリダイレクトさせる
if (!$session_userId) {
  header("Location:http://localhost/login/login.php");
  exit();
}

// ツイートのidがあればその値を取得し表示、なければnullを代入する
if (isset($_GET['tweet_id'])) {
  $tweet_id = $_GET['tweet_id'];
  $stmt_tweet = $db->prepare("SELECT 
                        tweets.id,first_name,last_name,tweet
                       FROM
                        users
                      INNER JOIN
                        tweets
                      ON 
                        users.id = tweets.user_id   
                      WHERE 
                        tweets.id = ?");
  $stmt_tweet->execute([$tweet_id]);
  $row_tweet = $stmt_tweet->fetch();
} else {
  $tweet_id = null;
}

// リプライの一覧表示
$stmt_reply = $db->prepare("SELECT 
                        reply,tweet_id,user_id,first_name,last_name
                      FROM 
                        users
                      INNER JOIN
                        replys
                      ON
                        users.id = replys.user_id
                      WHERE
                        tweet_id = ?
                      ORDER BY
                        replys.id desc
");
$stmt_reply->execute([$tweet_id]);
$row_reply = $stmt_reply->fetchAll(PDO::FETCH_ASSOC);

// リプライを取得してrplysテーブルに追加する
if (!empty($_POST)) {
  $reply_post = filter_input(INPUT_POST, 'reply', FILTER_SANITIZE_SPECIAL_CHARS);
  $stmt_insert_reply = $db->prepare("INSERT INTO
                                      replys (tweet_id,user_id,reply)
                                    VALUES(:tweet_id,:user_id,:reply)
  ");
  $stmt_insert_reply->bindValue(':tweet_id', $tweet_id);
  $stmt_insert_reply->bindValue(':user_id', $session_userId);
  $stmt_insert_reply->bindValue(':reply', $reply_post);
  $stmt_insert_reply->execute();
  header("Location: http://localhost/reply/reply.php?tweet_id=" . urlencode($tweet_id));
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/destyle.css@1.0.15/destyle.css" />
  <link rel="stylesheet" href="reply.css">
  <title>リプライ画面</title>
</head>

<body>
  <div class="wrapper">
    <h1><?php echo htmlspecialchars($session_firstName, ENT_QUOTES) . htmlspecialchars($session_lastName, ENT_QUOTES); ?></h1>
    <div class="flex">
      <main class="main">
        <div class="tweetCard">
          <p class="name">
            <?php echo htmlspecialchars($row_tweet['first_name'], ENT_QUOTES) . htmlspecialchars($row_tweet['last_name'], ENT_QUOTES); ?>
          </p>
          <p class="tweet"><?php echo htmlspecialchars($row_tweet['tweet'], ENT_QUOTES); ?></p>
        </div>
        <form method="post" class="reply_form">
          <textarea name="reply" id="reply" class="reply"></textarea>
          <input type="submit" value="返信する" class="btn">
        </form>
        <div class="reply_list">
          <?php foreach ($row_reply as $reply): ?>
            <div class="reply_item">
              <p class="name">
                <?php echo htmlspecialchars($reply['first_name']) . htmlspecialchars($reply['last_name']); ?>
              </p>
              <p class="tweet"><?php echo htmlspecialchars($reply['reply'], ENT_QUOTES); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </main>
      <aside class="aside">
        <li class="aside__item"><a href="http://localhost/top/top.php">ホーム</a></li>
        <li class="aside__item"><a href="../profile_edit/profile_edit.php">プロフィール</a></li>
        <a href="../logout/logout.php">ログアウトする</a>
      </aside>
    </div>
  </div>
</body>

</html>