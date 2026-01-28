<?php
/**
 * ログインページ
 * ユーザー認証を行い、セッションにログイン情報を保存する
 */

// セッション設定を読み込み（AWS ElastiCache Redis用）
require_once __DIR__ . '/config/session.php';

// セッションを開始
session_start();

// 既にログインしている場合はタイムラインにリダイレクト
if (!empty($_SESSION['login_user_id'])) {
  header("HTTP/1.1 302 Found");
  header("Location: ./timeline.php");
  return;
}

// エラーメッセージ用の変数を初期化
$error = '';

// データベースに接続（Docker Composeのmysqlサービスに接続）
$dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');

// POSTリクエストの場合、ログイン処理を実行
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  // フォームから送信されたユーザー名とパスワードを取得
  $name = '';
  $password = '';
  
  if (isset($_POST['name'])) {
    $name = $_POST['name'];
  }
  if (isset($_POST['password'])) {
    $password = $_POST['password'];
  }

  // バリデーション: ユーザー名とパスワードが入力されているかチェック
  if ($name == '' || $password == '') {
    $error = 'ユーザー名とパスワードを入力してください。';
  } else {
    // データベースからユーザー情報を取得（プリペアドステートメントでSQLインジェクション対策）
    $select_sth = $dbh->prepare("SELECT * FROM users WHERE name = :name");
    $select_sth->execute([':name' => $name]);
    $user = $select_sth->fetch();

    // パスワード検証: password_verify()でハッシュ化されたパスワードと一致するか確認
    if (!empty($user) && password_verify($password, $user['password'])) {
      // ログイン成功: セッションにユーザーIDを保存
      $_SESSION['login_user_id'] = $user['id'];
      header("HTTP/1.1 302 Found");
      header("Location: ./timeline.php");
      return;
    } else {
      // ログイン失敗: エラーメッセージを設定
      $error = 'ユーザー名またはパスワードが正しくありません。';
    }
  }
}

// ヘッダーをインクルード
require_once __DIR__ . '/includes/header.php';
?>
<h1>ログイン</h1>

<?php if ($error): ?>
  <p><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
  <div>
    <label>ユーザー名</label>
    <input type="text" name="name" required>
  </div>
  <div>
    <label>パスワード</label>
    <input type="password" name="password" required>
  </div>
  <button type="submit">ログイン</button>
</form>

<p><a href="/register.php">新規登録はこちら</a></p>
