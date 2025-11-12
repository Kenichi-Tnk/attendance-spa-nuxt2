 勤怠管理システム SPA (Nuxt.js 2)

![Nuxt.js](https://img.shields.io/badge/Nuxt.js-2.18.1-00C58E.svg)
![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20.svg)
![Vue.js](https://img.shields.io/badge/Vue.js-2.x-4FC08D.svg)
![PHP](https://img.shields.io/badge/PHP-8.4.10-777BB4.svg)
![Node.js](https://img.shields.io/badge/Node.js-LTS-339933.svg)

このプロジェクトは Nuxt.js 2.15.8 で構築された Single Page Application（SPA）です。
Laravel バックエンド API と連携した勤怠管理システムを提供します。

## 📋 目次

- [機能概要](#機能概要)
- [技術スタック](#技術スタック)
- [必要な環境](#必要な環境)
- [セットアップ手順](#セットアップ手順)
- [プロジェクト構成](#プロジェクト構成)
- [API 仕様](#api仕様)
- [使用方法](#使用方法)
- [開発者向け情報](#開発者向け情報)
- [トラブルシューティング](#トラブルシューティング)

## 🚀 機能概要

### 一般ユーザー機能

- **認証・認可**
  - ユーザー登録・ログイン
  - メール認証
  - JWT による認証管理
- **勤怠管理**

  - 出勤・退勤打刻
  - 休憩時間の記録
  - 労働時間の自動計算
  - 勤怠データの一覧表示

- **補正申請**
  - 打刻時刻の修正申請
  - 休憩時間の修正申請
  - 申請理由の記入
  - 申請状態の確認

### 管理者機能

- **ユーザー管理**
  - 全ユーザーの勤怠データ確認
- **補正申請管理**
  - 申請内容の確認
  - 承認・却下処理
  - 却下理由の記入

## 🛠 技術スタック

### フロントエンド

- **Nuxt.js 2.18.1** - Vue.js ベースのユニバーサル JavaScript フレームワーク
- **Vue.js 2.x** - プログレッシブ JavaScript フレームワーク
- **Vuex** - Vue.js の状態管理ライブラリ
- **Axios** - HTTP クライアント
- **Tailwind CSS** - ユーティリティファーストの CSS フレームワーク

### バックエンド

- **Laravel 8.x** - PHP ウェブアプリケーションフレームワーク
- **Laravel Sanctum** - API 認証システム
- **MySQL** - リレーショナルデータベース

### 開発環境

- **PHP 8.4.10**
- **Node.js LTS**
- **Composer** - PHP パッケージマネージャー
- **npm** - Node.js パッケージマネージャー

## 📋 必要な環境

開発・実行環境として以下が必要です：

### 必須要件

- **Node.js** v16.x 以上 (LTS 推奨)
- **npm** v7.x 以上
- **PHP** v8.1 以上 (8.4.10 推奨)
- **Composer** v2.x 以上
- **MySQL** v8.0 以上

### 推奨環境

- **macOS** / **Linux** / **Windows 10/11**
- **Git** v2.x 以上
- モダンブラウザ (Chrome, Firefox, Safari, Edge)

## 🔧 セットアップ手順

### 既存プロジェクトを使用する場合

#### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd attendance-spa-nuxt2
```

### 新規で Nuxt.js 2 プロジェクトを作成する場合

もしゼロから Nuxt.js 2 プロジェクトを作成する場合は、以下のコマンドを使用します：

````bash
新しいNuxt.js 2プロジェクトを作成する場合：

```bash
# create-nuxt-appのバージョン2を使用（Nuxt 2用）
npx create-nuxt-app@2 my-nuxt2-app

# または
npm init nuxt-app@2 my-nuxt2-app

# または
yarn create nuxt-app@2 my-nuxt2-app
````

このプロジェクトと同様の構成で作成する場合の選択例：

- Programming language: JavaScript
- Package manager: npm
- UI framework: Tailwind CSS
- Nuxt.js modules: Axios
- Linting tools: なし（このプロジェクトでは未使用）
- Testing framework: なし
- Rendering mode: **Single Page App**
- Deployment target: Static (JAMStack hosting)

作成後、このプロジェクトと同じバージョンを使用する場合：

```bash
cd my-nuxt2-app
npm install nuxt@2.15.8
```

```

**create-nuxt-app での設定例**：

```

? Project name: attendance-spa-nuxt2
? Programming language: JavaScript
? Package manager: Npm
? UI framework: None
? Nuxt.js modules: Axios - Promise based HTTP client
? Linting tools: ESLint, Prettier
? Testing framework: None
? Rendering mode: Single Page App
? Deployment target: Static (Static/Jamstack hosting)
? Development tools: None
? Continuous integration: None
? Version control system: Git

````

**注意**: 現在のプロジェクトは既にセットアップ済みなので、上記コマンドは新規作成時のみ使用してください。

### 2. バックエンド (Laravel API) のセットアップ

#### 2.1. Laravel プロジェクトの準備

```bash
cd attendance-api
composer install
````

#### 2.2. 環境設定ファイルの作成

```bash
cp .env.example .env
```

`.env` ファイルを編集し、データベース接続情報を設定：

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_spa
DB_USERNAME=your_username
DB_PASSWORD=your_password

APP_KEY=
JWT_SECRET=your_jwt_secret
APP_URL=http://localhost:8000
```

#### 2.3. アプリケーションキーの生成

```bash
php artisan key:generate
```

#### 2.4. データベースの作成とマイグレーション

```bash
# データベースを作成 (MySQLコンソールまたはphpMyAdmin等で)
CREATE DATABASE attendance_spa;

# マイグレーション実行
php artisan migrate

# 初期データの投入（必要に応じて）
php artisan db:seed
```

#### 2.5. Laravel 開発サーバーの起動

```bash
php artisan serve
# サーバーが http://localhost:8000 で起動します
```

### 3. フロントエンド (Nuxt.js) のセットアップ

#### 3.1. プロジェクトディレクトリに移動

```bash
# 新しいターミナルを開いて
cd /path/to/attendance-spa-nuxt2
```

#### 3.2. Nuxt.js バージョンの確認

このプロジェクトで使用されている Nuxt.js のバージョンを確認できます：

```bash
# package.jsonでNuxtのバージョン確認
cat package.json | grep '"nuxt":'
# 出力例: "nuxt": "^2.15.8",

# または、インストール後に確認
npx nuxt --version
```

#### 3.3. Node.js パッケージのインストール

```bash
npm install

# 依存関係の詳細確認
npm list nuxt
# 出力例: attendance-spa-nuxt2@1.0.0 └── nuxt@2.15.8
```

#### 3.3. Nuxt.js 開発サーバーの起動

```bash
npm run dev
```

アプリケーションが http://localhost:3000 で起動します。

### 4. テスト用アカウント（オプション）

システムをすぐにテストしたい場合は、以下のシーダーを実行してテストデータを作成できます：

```bash
cd attendance-api
php artisan db:seed --class=UserSeeder
```

**テスト用アカウント**:

- **一般ユーザー**:
  - メール: `attendance-test@example.com`
  - パスワード: `password123`
- **管理者ユーザー**:
  - メール: `admin@example.com`
  - パスワード: `password123`

## 📁 プロジェクト構成

```
attendance-spa-nuxt2/
├── attendance-api/               # Laravel API バックエンド
│   ├── app/
│   │   ├── Http/Controllers/API/ # API コントローラー
│   │   ├── Models/              # Eloquent モデル
│   │   └── Http/Middleware/     # ミドルウェア
│   ├── database/
│   │   ├── migrations/          # データベースマイグレーション
│   │   └── seeders/            # シーダーファイル
│   ├── routes/
│   │   └── api.php             # API ルート定義
│   └── config/                 # 設定ファイル
├── assets/                     # コンパイル前アセット
│   └── css/                   # カスタム CSS ファイル
├── components/                # Vue コンポーネント
├── layouts/                   # レイアウトファイル
├── middleware/               # Nuxt.js ミドルウェア
├── pages/                    # ページコンポーネント（自動ルーティング）
├── plugins/                  # プラグイン
├── static/                   # 静的ファイル
├── store/                    # Vuex ストア
└── nuxt.config.js           # Nuxt.js 設定ファイル
```

### 重要なファイル

#### フロントエンド

- `store/auth.js` - 認証状態管理
- `middleware/auth.js` - 認証ミドルウェア
- `plugins/auth.js` - 認証プラグイン
- `pages/login.vue` - ログインページ
- `pages/dashboard.vue` - ダッシュボード
- `pages/correction-requests/` - 補正申請関連ページ

#### バックエンド

- `app/Http/Controllers/API/AuthController.php` - 認証コントローラー
- `app/Http/Controllers/API/AttendanceController.php` - 勤怠管理コントローラー
- `app/Http/Controllers/API/AttendanceCorrectController.php` - 補正申請コントローラー
- `app/Models/User.php` - ユーザーモデル
- `app/Models/Attendance.php` - 勤怠データモデル

## 🔌 API 仕様

### 認証エンドポイント

```
POST /api/register          # ユーザー登録
POST /api/login            # ログイン
GET  /api/user             # 認証済みユーザー情報取得
POST /api/logout           # ログアウト
```

### 勤怠管理エンドポイント

```
GET    /api/attendance                    # 勤怠データ一覧取得
POST   /api/attendance/check-in          # 出勤打刻
POST   /api/attendance/check-out         # 退勤打刻
POST   /api/attendance/rest-start        # 休憩開始
POST   /api/attendance/rest-end          # 休憩終了
```

### 補正申請エンドポイント

```
GET    /api/correction-requests          # 補正申請一覧取得
POST   /api/correction-requests          # 補正申請作成
GET    /api/correction-requests/{id}     # 補正申請詳細取得
POST   /api/correction-requests/{id}/approve  # 承認 (管理者のみ)
POST   /api/correction-requests/{id}/reject   # 却下 (管理者のみ)
```

## 💻 使用方法

### 1. アプリケーションへのアクセス

ブラウザで http://localhost:3000 にアクセスします。

### 2. ユーザー登録・ログイン

1. 「新規登録」からアカウントを作成
2. メール認証を完了
3. ログイン画面からログイン

### 3. 勤怠打刻

1. ダッシュボードの「出勤」ボタンで出勤打刻
2. 「休憩開始」「休憩終了」で休憩時間を記録
3. 「退勤」ボタンで退勤打刻

### 4. 補正申請

1. 「補正申請」メニューから申請画面へ
2. 修正したい日付と時刻を入力
3. 申請理由を記入して送信

### 5. 管理者機能（管理者ロールのユーザーのみ）

1. 「管理者ダッシュボード」から申請一覧を確認
2. 各申請の詳細を確認
3. 承認または却下を実行

## 🛠 開発者向け情報

### 開発環境の起動

```bash
# バックエンド（1つ目のターミナル）
cd attendance-api
php artisan serve

# フロントエンド（2つ目のターミナル）
cd attendance-spa-nuxt2
npm run dev
```

### ホットリロードでの開発

Nuxt.js の開発サーバーはファイル変更を自動検知し、ブラウザを自動更新します。

### API テスト

Postman や curl を使用して API エンドポイントをテストできます：

```bash
# ログインテスト
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### データベース操作

```bash
# マイグレーションの作成
cd attendance-api
php artisan make:migration create_example_table

# マイグレーション実行
php artisan migrate

# マイグレーションのロールバック
php artisan migrate:rollback

# データベースのリセット（全マイグレーション再実行）
php artisan migrate:fresh

# シーダー実行
php artisan db:seed

# 特定のシーダー実行
php artisan db:seed --class=UserSeeder
```

### よく使うコマンド

```bash
# Laravel関連
cd attendance-api
php artisan serve                    # 開発サーバー起動
php artisan route:list              # ルート一覧表示
php artisan make:controller ExampleController --api  # APIコントローラー作成
php artisan make:model Example -m   # モデルとマイグレーション作成
php artisan tinker                  # Laravelコンソール

# Nuxt.js関連
npm run dev                         # 開発サーバー起動
npm run build                       # 本番用ビルド
npm run generate                    # 静的サイト生成
npm run lint                        # コードチェック

# Git関連
git status                          # 変更ファイル確認
git add .                           # 全変更をステージング
git commit -m "commit message"      # コミット
git push origin develop             # プッシュ
```

## ❗ トラブルシューティング

### よくある問題と解決方法

#### 1. CORS エラー

**症状**: ブラウザで「CORS policy」エラーが発生

**解決方法**:

- Laravel API サーバーが正常に起動しているか確認
- `attendance-api/config/cors.php` の設定を確認
- ブラウザのキャッシュをクリア

#### 2. 認証エラー (419 CSRF Token Mismatch)

**症状**: ログイン時に 419 エラーが発生

**解決方法**:

- 通常は初回のみ発生し、2 回目以降は正常に動作します
- ページを再読み込みして再試行してください

#### 3. データベース接続エラー

**症状**: 「Database connection failed」エラー

**解決方法**:

```bash
# データベースサービスの確認
mysql --version
sudo service mysql start  # Linux の場合
brew services start mysql # macOS の場合

# .env ファイルの設定確認
cat attendance-api/.env
```

#### 4. Node.js / npm エラー

**症状**: `npm install` でエラーが発生

**解決方法**:

```bash
# Node.js バージョン確認
node --version
npm --version

# キャッシュクリア
npm cache clean --force

# node_modules を削除して再インストール
rm -rf node_modules package-lock.json
npm install
```

#### 5. PHP / Composer エラー

**症状**: `composer install` でエラーが発生

**解決方法**:

```bash
# PHP バージョン確認
php --version

# Composer 更新
composer self-update

# 依存関係の再インストール
rm -rf vendor composer.lock
composer install
```

### ログの確認

#### Laravel ログ

```bash
tail -f attendance-api/storage/logs/laravel.log
```

#### Nuxt.js 開発サーバーログ

コンソールに出力されます。ブラウザの開発者ツールでもエラーを確認できます。

### パフォーマンス最適化

#### 本番環境向けビルド

```bash
# フロントエンド
npm run build
npm run start

# バックエンド
cd attendance-api
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
