# 模擬案件\_フリマアプリ

## 環境構築

**Docker ビルド**

1. `git@github.com:StudioRocha/mock_case1.git`
2. `docker-compose up -d --build`

※補足: MySQL は OS によって起動しない場合があるため、各 PC に合わせて docker-compose.yml を編集してください。

**Laravel 環境構築**

1. コマンドライン上

```bash
 docker-compose exec php bash
```

2. PHP コンテナ上

```bash
 composer install
```

3. .env.example から .env を作成し、環境変数を設定
4. .env に以下の環境変数を追加

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

5. アプリケーションキーの作成

```bash
php artisan key:generate
```

6. マイグレーションの実行

```bash
php artisan migrate
```

7. シーディングの実行

### 概要

シーディング（Seeding）は、データベースに初期データを投入する処理です。
開発・テスト環境で必要なサンプルデータを自動生成します。

### 実行コマンド

```bash
 php artisan db:seed
```

### 生成されるデータ

#### カテゴリデータ

-   **テーブル**: `categories`
-   **データ数**: 5 件
-   **内容**:
    -   エレクトロニクス
    -   ファッション
    -   ホーム&ガーデン
    -   スポーツ&アウトドア
    -   本&メディア

#### 商品データ

-   **テーブル**: `items`
-   **データ数**: 10 件
-   **内容**:
    -   固定商品 10 点を事前定義
    -   各商品に 1-3 個のカテゴリをランダム割り当て
    -   外部画像をローカルにダウンロードして保存
    -   サンプル商品例：
        -   腕時計 (¥15,000) - Rolax
        -   HDD (¥5,000) - 西芝
        -   ノート PC (¥45,000)
        -   コーヒーミル (¥4,000) - Starbacks
        -   メイクセット (¥2,500)

#### ユーザーデータ

-   **テーブル**: `users`
-   **データ数**: 11 件
-   **内容**:
    -   固定ユーザー: dev@example.com（パスワード: password）
    -   ランダムユーザー: 10 件（パスワード: password）
    -   全ユーザーがメール認証済み状態

#### プロフィールデータ

-   **テーブル**: `profiles`
-   **データ数**: 11 件
-   **内容**:
    -   各ユーザーに対応するプロフィール情報
    -   住所、電話番号、生年月日を含む

### 注意事項

-   シーディングは開発・テスト環境でのみ実行してください
-   本番環境では実行しないでください
-   既存データがある場合は、事前にバックアップを取ってください
-   商品画像は外部 URL から自動ダウンロードされます

## 8. テストケースの実施

### テスト用データベースの準備

Feature テストを実行するために、テスト用データベースの準備が必要です。

#### 1. テスト用データベースの作成

```bash
mysql -u root -p -e 'CREATE DATABASE laravel_test_db;'
```

#### 2. テスト用データベースのマイグレーション

```bash
php artisan migrate:fresh --env=testing
```

### テスト実行

```bash
# Featureテスト
./vendor/bin/phpunit tests/Feature

# Unitテスト
./vendor/bin/phpunit tests/Unit

# 全テスト
./vendor/bin/phpunit
```

### 注意事項

-   上記コマンドは PHP コンテナ内で実行してください
-   テスト用データベース（`laravel_test_db`）は本番データベース（`laravel_db`）とは別です
-   `RefreshDatabase`トレイトにより、テスト実行時に自動的にデータベースがリフレッシュされます

<BR>

![Warning](https://img.shields.io/badge/Warning-Permission%20denied-red)
UnexpectedValueException ページエラーが出た場合

Windows WSL 環境ではファイル権限のエラーが出る場合があります。
ブラウザで localhost 接続時に "Permission denied"というエラーが発生した場合は、
このプロジェクトの root ディレクトリで下記のコマンドを実行して再接続して下さい。

```bash
$ sudo chmod -R 777 src/*
```

## 使用技術(実行環境)

-   php:8.1-fpm
-   Laravel 8.83.8
-   MySQL 8.0.26

## ER 図

<img src="docs/images/ER.svg" width="640" alt="ER図">

ソース（編集用）: [docs/ER.dio]

## URL

-   開発環境：http://localhost/
-   phpMyAdmin:：http://localhost:8080/
