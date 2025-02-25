<?php
  try {
    $db = new PDO('mysql:dbname=twitter;host=127.0.0.1;charset=utf8','root','');
    $db->query('SET NAMES utf8;');
  } catch (PDOException $e) {
    echo 'DB接続エラー'. $e -> getMessage();
  }
?>