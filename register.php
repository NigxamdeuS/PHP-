<?php
/**
 * 会員登録ページ
 * 新規ユーザーの登録処理を行う
 */

// セッション設定を読み込み（AWS ElastiCache Redis用）
require_once __DIR__ . '/config/session.php';

// セッションを開始
session_start();

// データベースに接続（Docker Composeのmysqlサービスに接続）
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// エラーメッセージと成功メッセージ用の変数を初期化
$error = '';
$success = '';

// POSTリクエストの場合、登録処理を実行
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // フォームから送信されたユーザー名、パスワード、パスワード確認を取得
  $name = '';
  $password = '';
  $password_confirm = '';
  
  if (isset($_POST['name'])) {
    $name = $_POST['name'];
  }
  if (isset($_POST['password'])) {
    $password = $_POST['password'];
  }
  if (isset($_POST['password_confirm'])) {
    $password_confirm = $_POST['password_confirm'];
  }

  // バリデーション: 必須項目のチェック
  if ($name == '' || $password == '') {
    $error = 'ユーザー名とパスワードを入力してください。';
  } else if ($password != $password_confirm) {
    // パスワード確認: パスワードと確認用パスワードが一致するかチェック
    $error = 'パスワードが一致しません。';
  } else {
    // 重複チェック: 既に存在するユーザー名かどうかを確認（プリペアドステートメントでSQLインジェクション対策）
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE name = :name");
    $select_sth->execute([':name' => $name]);
    $existing = $select_sth->fetch();
    
    if (!empty($existing)) {
      $error = 'このユーザー名は既に使用されています。';
    } else {
      // パスワードをハッシュ化してデータベースに登録
      // password_hash()でPASSWORD_DEFAULTアルゴリズムを使用してハッシュ化
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $insert_sth = $dbh->prepare("INSERT INTO users (name, password) VALUES (:name, :password)");
      $insert_sth->execute([':name' => $name, ':password' => $password_hash]);
      $success = '登録が完了しました。ログインしてください。';
    }
  }
}

// ヘッダーをインクルード
require_once __DIR__ . '/includes/header.php';
?>
<h1>会員登録</h1>

<?php if ($error): ?>
  <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
  <p><?= htmlspecialchars($success) ?></p>
  <p><a href="/login.php">ログインページへ</a></p>
<?php else: ?>
  <form method="POST">
    <div>
      <label>ユーザー名</label>
      <input type="text" name="name" required>
    </div>
    <div>
      <label>パスワード</label>
      <input type="password" name="password" required>
    </div>
    <div>
      <label>パスワード（確認）</label>
      <input type="password" name="password_confirm" required>
    </div>
    <button type="submit">登録</button>
  </form>

  <p><a href="/login.php">ログインはこちら</a></p>
<?php endif; ?>
