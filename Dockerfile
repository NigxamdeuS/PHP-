FROM php:8.2-fpm

# 必要なパッケージをインストール（Redis拡張のビルドに必要）
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# PDO、MySQL拡張をインストール
RUN docker-php-ext-install pdo pdo_mysql

# phpredis拡張をインストール
RUN pecl install redis && \
    docker-php-ext-enable redis

# 作業ディレクトリを設定
WORKDIR /var/www

# 画像アップロード用のディレクトリを作成
# post.phpとtimeline.phpは/var/www/upload/image/に保存するため、
# public/upload/image/へのシンボリックリンクを作成
RUN mkdir -p /var/www/public/upload/image && \
    mkdir -p /var/www/upload && \
    ln -s /var/www/public/upload/image /var/www/upload/image
