<?php
session_start();
require_once('../db_connect.php');


if (!empty($_POST)) {

  define('MSG01', '入力必須です。');
  define('MSG02', '無効なメールアドレスです。');
  define('MSG03', 'メールアドレスまたはパスワードが間違っています。');
  $err_msg = array();

  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password1 = filter_input(INPUT_POST, 'password1');


  // 空白のバリデーション
  if (!$email) {
    $err_msg['email'] = MSG01;
  }
  if (!$password1) {
    $err_msg['password1'] = MSG01;
  }

  // $err_msgが空だったら
  if (empty($err_msg)) {
    // メールアドレスのバリデーション
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $err_msg['email'] = MSG02;
    }

    // データベースから一致するメールアドレスを取得する
    $stmt = $db->prepare("select * from users where LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->execute([$email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!isset($row)) {
      $err_msg['email'] = MSG03;
    }

    $hashedPassword = hash('sha256', $password1);
    if (!($hashedPassword === $row['password'])) {
      $err_msg['email'] = MSG03;
    } else {
      $_SESSION['user_id'] = $row['id'];
      $_SESSION['first_name'] = $row['first_name'];
      $_SESSION['last_name'] = $row['last_name'];
      $_SESSION['email'] = $email;
      header('Location:http://localhost/top/top.php');
      exit();
    }
  }
}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/destyle.css@1.0.15/destyle.css" />
  <link rel="stylesheet" href="login.css">
  <title>ログイン</title>
</head>

<body>
  <h1>ログインしてください</h1>
  <form action="" method="post" class="loginForm">
    <div class="email">
      <label for="email">メールアドレス</label>
      <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'], ENT_QUOTES) ?? ""; ?>">
      <span class="err--msg"><?php if (!empty($err_msg['email'])) echo htmlspecialchars($err_msg['email'], ENT_QUOTES); ?></span>
    </div>
    <div class="password">
      <div class="password1">
        <label for="password1">パスワード</label>
        <input type="password" name="password1" id="password1" value="<?php echo htmlspecialchars($_POST['password1'], ENT_QUOTES) ?? ""; ?>">
        <span class="err--msg"><?php if (!empty($err_msg['password1'])) echo htmlspecialchars($err_msg['password1'], ENT_QUOTES); ?></span>
      </div>
    </div>
    <input type="submit" value="ログインする" class="btn">
  </form>
  <p><a href="../register/register.php">初めての方はこちら</a></p>
</body>

</html>