# 手順書

---

## 1. Git のインストール（git clone する場合）

ソースコードを GitHub から取得するために Git を使用する場合、インストールします

## 2. Docker のインストール

- **Dockerdesktopダウンロードし、インストール手順に従ってセットアップする。

インストール後、ターミナルで次を実行し、Docker と Docker Compose が使えることを確認する。

```bash
docker -v
docker compose -v
```


---

## 3. ソースコードの配置

リポジトリを clone してプロジェクトを配置する。

```bash
git clone https://github.com/NigxamdeuS/PHP-.git
```

---

## 4. 起動方法

プロジェクトのルートディレクトリで、次を実行する。

```bash
docker compose up -d --build
```

停止するとき:

```bash
docker compose down
```

---

## 5. DB のテーブル作成方法

アプリケーションで使用するテーブルは、PHP の初期化スクリプトで作成する。

```bash
docker compose exec php php /var/www/public/config/init_db.php
```

作成されるテーブルは次の4つです

- **users** … 会員情報
- **posts** … 投稿内容
- **post_images** … 投稿に紐づく画像
- **follows** … フォロー関係

---

## 動作確認の流れ（まとめ）

1. Git をインストール（必要な場合）
2. Docker をインストール
3. `git clone` でソースを取得
4. 必要に応じて `compose.yml` のボリュームをリポジトリ構成に合わせて修正
5. `docker compose up -d --build` で起動
6. MySQL 起動後、`docker compose exec php php ... config/init_db.php` でテーブル作成
7. ブラウザで http://localhost にアクセスして動作確認
