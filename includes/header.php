<?php
/**
 * ヘッダーコンポーネント
 * 全ページ共通のヘッダー部分を表示する
 * ログインしている場合のみ表示される
 */

// セッション設定を読み込み（AWS ElastiCache Redis用）
require_once __DIR__ . '/../config/session.php';

// セッションが開始されていない場合は開始
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ログインしている場合のみヘッダーを表示
if (!empty($_SESSION['login_user_id'])) {
    // データベースに接続して現在のユーザー情報を取得
    $dbh = new PDO('mysql:host=mysql;dbname=example_db', 'root', '');
    $select_sth = $dbh->prepare("SELECT name FROM users WHERE id = :id");
    $select_sth->execute([':id' => $_SESSION['login_user_id']]);
    $current_user = $select_sth->fetch();
    ?>
    <header class="main-header">
        <div class="header-container">
            <a href="/timeline.php" class="logo"></a>
            <nav class="main-nav">
                <a href="/timeline.php" class="nav-link">タイムライン</a>
                <a href="/search.php" class="nav-link">検索</a>
                <a href="/post.php" class="nav-link">投稿</a>
                <a href="/logout.php" class="nav-link">ログアウト</a>
            </nav>
            <div class="user-info">
                <?php if (!empty($current_user)): ?>
                    <span><?= htmlspecialchars($current_user['name']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <?php
}
?>
