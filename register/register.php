<?php
session_start();
require_once('../db_connect.php');
if (!empty($_POST)) {

  // エラーメッセージを定義
  define('MSG01', '入力必須です。');
  define('MSG02', '無効なメールアドレスです。');
  define('MSG03', 'このメールアドレスはすでに存在しています。');
  define('MSG04', 'パスワードは英数字8文字以上にして下さい。');
  define('MSG05', 'パスワード(再入力)が間違っています。');

  // エラー変数
  $err_msg = array();

  // 入力内容の取得
  $firstName = filter_input(INPUT_POST, 'firstName');
  $lastName = filter_input(INPUT_POST, 'lastName');
  $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
  $password1 = filter_input(INPUT_POST, 'password1');
  $password2 = filter_input(INPUT_POST, 'password2');
  // 空の場合のバリデーション
  if (!$firstName) {
    $err_msg['firstName'] = MSG01;
  }
  if (!$lastName) {
    $err_msg['lastName'] = MSG01;
  }
  if (!$email) {
    $err_msg['email'] = MSG01;
  }
  if (!$password1) {
    $err_msg['password1'] = MSG01;
  }
  if (!$password2) {
    $err_msg['password2'] = MSG01;
  }
  // 上のエラーがなかった時の処理
  if (empty($err_msg)) {

    // メールアドレスのバリデーション
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $err_msg['email'] = MSG02;
    }

    // パスワードと再入力パスワードが一致しない場合
    if ($password1 !== $password2) {
      $err_msg['password1'] = MSG05;
    }

    // ここまでエラーがなかった時
    if (empty($err_msg)) {
      // パスワードの正規表現
      if (!preg_match('/\A[a-z\d]{8,100}+\z/i', $password1)) {
        $err_msg['password1'] = MSG04;
      }
      $hashedPassword = hash('sha256', $password1);
      // メールアドレスの重複確認
      // 受け取ったメールアドレスと一致するメールアドレスが存在していれば取得する 
      $stmt = $db->prepare("select email from users where LOWER(email) = LOWER(?) LIMIT 1");
      $stmt->execute([$email]);
      $fetchEmail = $stmt->fetch(PDO::FETCH_ASSOC);

      // 重複していれば、エラーを出し、そうでなければ登録する
      if (isset($fetchEmail['email'])) {
        $err_msg['email'] = MSG03;
      } else {
        $stmt = $db->prepare("insert into users(first_name,last_name,email, password) values(?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $hashedPassword]);
        header('Location: http://localhost/top/top.php');
        exit();
      }
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
  <link rel="stylesheet" href="register.css">
  <title>会員登録</title>
</head>

<body>
  <h1>会員登録をする</h1>
  <form action="" method="post" class="registerForm">
    <!-- 名前登録 -->
    <div class="form--name">
      <div class="firstName name">
        <label for="name">姓</label>
        <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($_POST['firstName'],ENT_QUOTES) ?? ""; ?>">
        <span class="err--msg"><?php if (!empty($err_msg['firstName'])) echo htmlspecialchars($err_msg['firstName'],ENT_QUOTES); ?></span>
      </div>
      <div class="lastName name">
        <label for="name">名</label>
        <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($_POST['lastName'],ENT_QUOTES) ?? ""; ?>">
        <span class="err--msg"><?php if (!empty($err_msg['lastName'])) echo htmlspecialchars($err_msg['lastName'],ENT_QUOTES); ?></span>
      </div>
    </div>
    <div class="email">
      <label for="email">メールアドレス</label>
      <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'],ENT_QUOTES) ?? ""; ?>">
      <span class="err--msg"><?php if (!empty($err_msg['email'])) echo htmlspecialchars($err_msg['email'],ENT_QUOTES); ?></span>
    </div>
    <div class="password">
      <div class="password1">
        <label for="password1">パスワード</label>
        <input type="password" name="password1" id="password1" value="<?php echo htmlspecialchars($_POST['password1'],ENT_QUOTES) ?? ""; ?>">
        <span class="err--msg"><?php if (!empty($err_msg['password1'])) echo htmlspecialchars($err_msg['password1'],ENT_QUOTES); ?></span>
      </div>
      <div class="password2">
        <label for="password2">パスワード再入力</label>
        <input type="password" name="password2" id="password2" value="<?php echo htmlspecialchars($_POST['password2'],ENT_QUOTES) ?? ""; ?>">
      </div>
    </div>
    <input type="submit" value="確認する" class="btn">
  </form>
  <div class="login">
    <p>アカウントをお持ちの場合</p>
    <p><a href="../login/login.php">ログイン</a></p>
  </div>
</body>

</html>