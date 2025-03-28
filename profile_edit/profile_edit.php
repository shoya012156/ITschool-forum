<?php
session_start();
require_once('../db_connect.php');

// セッションの名前を保存
$session_firstName = $_SESSION['first_name'];
$session_lastName = $_SESSION['last_name'];
$session_userId = $_SESSION['user_id'];
$session_email = $_SESSION['email'];

// メールアドレスのバリデーション
define('MSG01', '無効なメールアドレスです。');
define('MSG02', 'パスワードは英数字8文字以上にして下さい。');
define('MSG03', '新しいパスワードとパスワード(再入力)が一致しません。');
define('MSG04', 'パスワードが違います。');
$err_msg = array();

// 入力された値を取得
$input_firstName = filter_input(INPUT_POST, 'firstName');
$input_lastName = filter_input(INPUT_POST, 'lastName');
$input_email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$input_password = filter_input(INPUT_POST, 'password');
$input_password_new = filter_input(INPUT_POST, 'password_new');
$input_password_check = filter_input(INPUT_POST, 'password_check');

// 姓の変更
if ($input_firstName === null || $input_firstName === '') {
  $input_firstName = $session_firstName;
}

if ($session_firstName !== $input_firstName) {
  $stmt_edit_firstName = $db->prepare("UPDATE 
      users
    SET
      first_name = :first_name
    WHERE
      users.id = :user_id  
  ");
  $stmt_edit_firstName->bindValue(':first_name', $input_firstName);
  $stmt_edit_firstName->bindValue(':user_id', $session_userId);
  $stmt_edit_firstName->execute();
  $_SESSION['first_name'] = $input_firstName;
  header('Location:http://localhost/top/top.php');
  exit();
}

// 名の変更
if ($input_lastName === null || $input_lastName === '') {
  $input_lastName = $session_lastName;
}

if ($session_lastName !== $input_lastName) {
  $stmt_edit_lastName = $db->prepare("UPDATE 
      users
    SET
      last_name = :last_name
    WHERE
      users.id = :user_id  
  ");
  $stmt_edit_lastName->bindValue(':last_name', $input_lastName);
  $stmt_edit_lastName->bindValue(':user_id', $session_userId);
  $stmt_edit_lastName->execute();
  $_SESSION['last_name'] = $input_lastName;
  header('Location:http://localhost/top/top.php');
  exit();
}
// メールアドレスの変更
if ($input_email === null || $input_email === '') {
  $input_email = $session_email;
}

if ($session_email !== $input_email) {
  if (filter_var($input_email, FILTER_VALIDATE_EMAIL)) {
    $stmt_edit_email = $db->prepare("UPDATE 
        users
      SET
        email = :email
      WHERE
        users.id = :user_id  
    ");
    $stmt_edit_email->bindValue(':email', $input_email);
    $stmt_edit_email->bindValue(':user_id', $session_userId);
    $stmt_edit_email->execute();
    $_SESSION['email'] = $input_email;
    header('Location:http://localhost/top/top.php');
    exit();
  } else {
    $err_msg['email'] = MSG01;
  }
}

// パスワードの変更
// 現在のパスワードが一致するか確認
if ($input_password) {
  // 現在のパスワードを取得してくる
  $stmt_get_password = $db->prepare("SELECT
    users.password
  FROM
    users
  WHERE
    users.id = :user_id
  ");
  $stmt_get_password->bindValue(':user_id', $session_userId);
  $stmt_get_password->execute();
  $row_get_password = $stmt_get_password->fetch(PDO::FETCH_ASSOC);
  // 入力されたパスワードをハッシュ化
  $hashedPassword = hash('sha256', $input_password);
  // パスワード(現在)とパスワード(取得)が一致するか
  if ($hashedPassword === $row_get_password['password']) {
    // パスワードの正規表現
    if (!preg_match('/\A[a-z\d]{8,100}+\z/i', $input_password_new)) {
      $err_msg['password_new'] = MSG02;
    }
    // パスワードと再入力パスワードが一致しない場合
    if ($input_password_new !== $input_password_check) {
      $err_msg['password_new'] = MSG03;
    }
    if (empty($err_msg)) {
      // 新しいパスワードのハッシュ化
      $hashedPassword_new = hash('sha256', $input_password_new);
      // パスワードを上書きする
      $stmt_edit_password = $db->prepare("UPDATE
                                            users
                                          SET
                                            users.password = :user_password
                                          WHERE
                                            users.id = :user_id
      ");
      $stmt_edit_password->bindValue(':user_password', $hashedPassword_new);
      $stmt_edit_password->bindValue(':user_id', $session_userId);
      $stmt_edit_password->execute();
      header('Location:http://localhost/top/top.php');
      exit();
    }
  } else {
    $err_msg['password'] = MSG04;
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/destyle.css@1.0.15/destyle.css" />
  <link rel="stylesheet" href="profile_edit.css">
  <title>プロフィール編集画面</title>
</head>

<body>
  <div class="wrapper">
    <div class="main">
      <div class="return_btn"><a href="http://localhost/top/top.php">トップページへ戻る</a></div>
      <form action="" method="post" class="edit_form">
        <div class="first_name">
          <label for="name">姓</label>
          <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($session_firstName, ENT_QUOTES) ?? ""; ?>">
        </div>
        <div class="last_name">
          <label for="name">名</label>
          <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($session_lastName, ENT_QUOTES) ?? ""; ?>">
        </div>
        <div class="email">
          <label for="email">メールアドレス</label>
          <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($session_email, ENT_QUOTES) ?? ""; ?>">
        </div>
        <div class="password">
          <label for="password">現在のパスワード</label>
          <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES) ?? ""; ?>">
          <span class="err--msg"><?php if (!empty($err_msg['password'])) echo htmlspecialchars($err_msg['password'], ENT_QUOTES); ?></span>
        </div>
        <div class="password">
          <label for="password_new">新しいパスワード</label>
          <input type="password" name="password_new" id="password_new" value="<?php echo htmlspecialchars($_POST['password_new'], ENT_QUOTES) ?? ""; ?>">
          <span class="err--msg"><?php if (!empty($err_msg['password_new'])) echo htmlspecialchars($err_msg['password_new'], ENT_QUOTES); ?></span>
        </div>
        <div class="password">
          <label for="password_check">パスワード再入力</label>
          <input type="password" name="password_check" id="password_check" value="<?php echo htmlspecialchars($_POST['password_check'], ENT_QUOTES) ?? ""; ?>">
        </div>
        <input type="submit" value="変更する" class="btn">
      </form>
    </div>
  </div>
</body>

</html>