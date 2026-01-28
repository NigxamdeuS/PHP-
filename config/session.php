<?php
/**
 * セッション管理
 * AWS ElastiCache (Redis) を使用したセッション管理
 * 
 * 環境変数:
 * - REDIS_HOST: Redisホスト（デフォルト: localhost）
 * - REDIS_PORT: Redisポート（デフォルト: 6379）
 * - REDIS_PASSWORD: Redisパスワード（オプション）
 * 
 * 注意: 実際のコードでは $_SESSION['login_user_id'] を使用しています
 */

// セッションが開始されていない場合のみ設定を適用
if (session_status() == PHP_SESSION_NONE) {
    // Redis接続情報を環境変数から取得（AWS ElastiCache用）
    $redis_host = getenv('REDIS_HOST') ?: 'localhost';
    $redis_port = getenv('REDIS_PORT') ?: 6379;
    $redis_password = getenv('REDIS_PASSWORD') ?: null;
    
    // Redisセッションハンドラを使用するように設定
    // AWS ElastiCacheのエンドポイントを使用する場合、ホスト名を環境変数で指定
    ini_set('session.save_handler', 'redis');
    
    // Redis接続文字列を構築
    $redis_connection = "tcp://{$redis_host}:{$redis_port}";
    if ($redis_password) {
        // パスワード認証が必要な場合
        $redis_connection = "tcp://{$redis_host}:{$redis_port}?auth={$redis_password}";
    }
    
    // セッション保存パスをRedisに設定
    ini_set('session.save_path', $redis_connection);
    
    // セッションの有効期限を設定（24時間）
    ini_set('session.gc_maxlifetime', 86400);
    
    // セッションクッキーの設定
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // HTTPSを使用する場合は1に変更
    ini_set('session.use_strict_mode', 1);
}
?>
