<?php
/**
 * ログアウトページ
 * セッションを破棄してログアウト処理を行う
 */

// セッション設定を読み込み（AWS ElastiCache Redis用）
require_once __DIR__ . '/config/session.php';

// セッションを開始（既に開始されている場合は何もしない）
session_start();

// セッション変数をクリア
$_SESSION = [];

// セッションクッキーが存在する場合は削除
// クッキーの有効期限を過去の日時に設定して削除
if (isset($_COOKIE[session_name()])) {
  setcookie(session_name(), '', time() - 42000, '/');
}

// セッションを完全に破棄
session_destroy();

// ログインページにリダイレクト
header("HTTP/1.1 302 Found");
header("Location: ./login.php");
return;
?>
