<?php
/**
 * フォロー解除ページ
 * 指定されたユーザーのフォローを解除する
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

// データベースに接続（Docker Composeのmysqlサービスに接続）
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// フォロー解除対象(フォローされる側)のユーザーデータを取得
$followee_user = null;
if (!empty($_GET['followee_user_id'])) {
  // 指定されたユーザーIDの会員情報を取得（プリペアドステートメントでSQLインジェクション対策）
  $select_sth = $dbh->prepare("SELECT * FROM users WHERE id = :id");
  $select_sth->execute([
      ':id' => $_GET['followee_user_id'],
  ]);
  $followee_user = $select_sth->fetch();
}
// ユーザーが存在しない場合は404エラーを返す
if (empty($followee_user)) {
  header("HTTP/1.1 404 Not Found");
  print("そのようなユーザーIDの会員情報は存在しません");
  return;
}

// 現在のフォロー状態をデータベースから取得
$select_sth = $dbh->prepare(
  "SELECT * FROM user_relationships"
  . " WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
);
$select_sth->execute([
  ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側（ログインユーザー）
  ':followee_user_id' => $followee_user['id'], // フォローされる側（フォロー解除対象）
]);
$relationship = $select_sth->fetch();
// フォローしていない場合（POSTリクエストでない場合のみエラー表示）
if (empty($relationship) && $_SERVER['REQUEST_METHOD'] != 'POST') {
  print("フォローしていません。");
  return;
}

// フォロー解除処理の結果を初期化
$delete_result = false;
// POSTリクエストの場合、実際のフォロー解除処理を実行
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (!empty($relationship)) {
    // フォロー関係を削除（プリペアドステートメントでSQLインジェクション対策）
    $delete_sth = $dbh->prepare(
      "DELETE FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
    );
    $delete_result = $delete_sth->execute([
      ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側（ログインユーザー）
      ':followee_user_id' => $followee_user['id'], // フォローされる側（フォロー解除対象）
    ]);
  }
}

// ヘッダーをインクルード
require_once __DIR__ . '/includes/header.php';
?>

<?php if($delete_result): ?>
<div>
  <?= htmlspecialchars($followee_user['name']) ?> さんのフォローを解除しました。<br>
  <a href="/profile.php?user_id=<?= $followee_user['id'] ?>">
    <?= htmlspecialchars($followee_user['name']) ?> さんのプロフィールに戻る
  </a>
</div>
<?php else: ?>
<div>
  <?= htmlspecialchars($followee_user['name']) ?> さんのフォローを解除しますか?
  <form method="POST">
    <button type="submit">
      フォロー解除する
    </button>
  </form>
</div>
<?php endif; ?>
