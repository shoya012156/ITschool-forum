<?php
  session_start();
  session_destroy();
  header("Location:http://localhost/login/login.php");
  exit();
?>

<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ログアウト</title>
</head>
<body>
  <h2>ログアウトが完了しました</h2>
</body>
</html>