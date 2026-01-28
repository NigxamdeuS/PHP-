<?php
/**
 * インデックスページ（トップページ）
 * ログイン状態に応じて適切なページにリダイレクトする
 */

// セッション設定を読み込み（AWS ElastiCache Redis用）
require_once __DIR__ . '/config/session.php';

// セッションを開始
session_start();

// ログインしていない場合はログインページにリダイレクト
if (empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./login.php");
  return;
}

// ログインしている場合はタイムラインにリダイレクト
header("HTTP/1.1 302 Found");
header("Location: ./timeline.php");
return;
?>
