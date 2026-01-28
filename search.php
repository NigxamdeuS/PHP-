<?php
/**
 * ユーザー検索ページ
 * ユーザー名で部分一致検索を行い、検索結果とフォロー状態を表示する
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

// 検索クエリと検索結果用の変数を初期化
$search_query = '';
$users = [];

// 検索クエリが指定されている場合、ユーザーを検索
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $search_query = $_GET['q'];
    // ユーザー名で部分一致検索（自分自身は除外、最大50件）
    // LIKE句で部分一致検索を実現（プリペアドステートメントでSQLインジェクション対策）
    $select_sth = $dbh->prepare(
        "SELECT * FROM users WHERE name LIKE :name AND id != :id ORDER BY name LIMIT 50"
    );
    $select_sth->execute([
        ':name' => '%' . $search_query . '%', // 前後に%を付けて部分一致検索
        ':id' => $_SESSION['login_user_id'] // 自分自身は除外
    ]);
    $users = $select_sth->fetchAll();
    
    // 各ユーザーのフォロー状態を取得（ログインユーザーがフォローしているかどうか）
    foreach ($users as &$user) {
        $follow_sth = $dbh->prepare(
            "SELECT * FROM user_relationships WHERE follower_user_id = :follower_user_id AND followee_user_id = :followee_user_id"
        );
        $follow_sth->execute([
            ':follower_user_id' => $_SESSION['login_user_id'], // フォローする側（ログインユーザー）
            ':followee_user_id' => $user['id'] // フォローされる側（検索結果のユーザー）
        ]);
        $user['relationship'] = $follow_sth->fetch(); // フォロー関係があれば取得、なければnull
    }
    unset($user); // 参照を解除
}

// ヘッダーをインクルード
require_once __DIR__ . '/includes/header.php';
?>

<h1>ユーザー検索</h1>

<form method="GET" action="">
  <input type="text" name="q" placeholder="ユーザー名を入力" value="<?= htmlspecialchars($search_query) ?>">
  <button type="submit">検索</button>
</form>

<?php if (!empty($search_query)): ?>
  <h2>検索結果</h2>
  <?php if (empty($users)): ?>
    <p>該当するユーザーが見つかりませんでした。</p>
  <?php else: ?>
    <?php foreach ($users as $user): ?>
      <div style="border: 1px solid #ccc; padding: 1em; margin-bottom: 1em;">
        <h3>
          <a href="/profile.php?user_id=<?= $user['id'] ?>">
            <?= htmlspecialchars($user['name']) ?>
          </a>
        </h3>
        <?php if(empty($user['relationship'])): ?>
          <a href="./follow.php?followee_user_id=<?= $user['id'] ?>">フォローする</a>
        <?php else: ?>
          フォロー中
          <a href="./unfollow.php?followee_user_id=<?= $user['id'] ?>">フォロー解除</a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
<?php endif; ?>
