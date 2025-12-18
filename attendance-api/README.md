# 勤怠管理システム API (Laravel)

Laravel 8 で構築された勤怠管理システムの REST API です。

## 🔌 API エンドポイント詳細

### 認証関連

#### ユーザー登録

```http
POST /api/register
Content-Type: application/json

{
  "name": "田中太郎",
  "email": "tanaka@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**レスポンス (201 Created)**:

```json
{
    "message": "User registered successfully. Please check your email for verification.",
    "user": {
        "id": 1,
        "name": "田中太郎",
        "email": "tanaka@example.com",
        "email_verified_at": null,
        "role": "user",
        "created_at": "2025-11-12T14:30:00.000000Z",
        "updated_at": "2025-11-12T14:30:00.000000Z"
    },
    "token": "1|abc123..."
}
```

#### ログイン

```http
POST /api/login
Content-Type: application/json

{
  "email": "tanaka@example.com",
  "password": "password123"
}
```

**レスポンス (200 OK)**:

```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "田中太郎",
        "email": "tanaka@example.com",
        "role": "user"
    },
    "token": "2|def456..."
}
```

#### 認証済みユーザー情報取得

```http
GET /api/user
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "user": {
        "id": 1,
        "name": "田中太郎",
        "email": "tanaka@example.com",
        "role": "user"
    }
}
```

#### ログアウト

```http
POST /api/logout
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "message": "Logout successful"
}
```

### 勤怠管理関連

#### 勤怠データ一覧取得

```http
GET /api/attendance
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "date": "2025-11-12",
            "check_in": "09:00:00",
            "check_out": "18:00:00",
            "total_work_time": "08:00:00",
            "rests": [
                {
                    "rest_start": "12:00:00",
                    "rest_end": "13:00:00"
                }
            ],
            "created_at": "2025-11-12T00:00:00.000000Z"
        }
    ],
    "per_page": 15,
    "total": 1
}
```

#### 出勤打刻

```http
POST /api/attendance/check-in
Authorization: Bearer {token}
```

**レスポンス (201 Created)**:

```json
{
    "message": "Check-in recorded successfully",
    "attendance": {
        "id": 2,
        "date": "2025-11-12",
        "check_in": "09:15:30",
        "check_out": null
    }
}
```

#### 退勤打刻

```http
POST /api/attendance/check-out
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "message": "Check-out recorded successfully",
    "attendance": {
        "id": 2,
        "date": "2025-11-12",
        "check_in": "09:15:30",
        "check_out": "18:30:45",
        "total_work_time": "08:15:15"
    }
}
```

#### 休憩開始

```http
POST /api/attendance/rest-start
Authorization: Bearer {token}
```

#### 休憩終了

```http
POST /api/attendance/rest-end
Authorization: Bearer {token}
```

### 補正申請関連

#### 補正申請一覧取得

```http
GET /api/correction-requests
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "current_page": 1,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "date": "2025-11-10",
            "check_in": "09:30:00",
            "check_out": "18:15:00",
            "reason": "電車遅延のため出勤時刻を修正",
            "status": "pending",
            "reject_reason": null,
            "user": {
                "name": "田中太郎"
            },
            "created_at": "2025-11-12T10:00:00.000000Z"
        }
    ]
}
```

#### 補正申請作成

```http
POST /api/correction-requests
Authorization: Bearer {token}
Content-Type: application/json

{
  "date": "2025-11-10",
  "check_in": "09:30:00",
  "check_out": "18:15:00",
  "reason": "電車遅延のため出勤時刻を修正",
  "rests": [
    {
      "rest_start": "12:00:00",
      "rest_end": "13:00:00"
    }
  ]
}
```

**レスポンス (201 Created)**:

```json
{
    "message": "Correction request created successfully",
    "correction_request": {
        "id": 2,
        "date": "2025-11-10",
        "check_in": "09:30:00",
        "check_out": "18:15:00",
        "reason": "電車遅延のため出勤時刻を修正",
        "status": "pending"
    }
}
```

#### 補正申請詳細取得

```http
GET /api/correction-requests/{id}
Authorization: Bearer {token}
```

#### 補正申請承認（管理者のみ）

```http
POST /api/correction-requests/{id}/approve
Authorization: Bearer {token}
```

**レスポンス (200 OK)**:

```json
{
    "message": "Correction request approved successfully",
    "correction_request": {
        "id": 1,
        "status": "approved"
    }
}
```

#### 補正申請却下（管理者のみ）

```http
POST /api/correction-requests/{id}/reject
Authorization: Bearer {token}
Content-Type: application/json

{
  "reject_reason": "申請内容に不備があります"
}
```

**レスポンス (200 OK)**:

```json
{
    "message": "Correction request rejected successfully",
    "correction_request": {
        "id": 1,
        "status": "rejected",
        "reject_reason": "申請内容に不備があります"
    }
}
```

## 🔐 認証

API は Laravel Sanctum を使用した Bearer Token 認証を採用しています。

### 認証ヘッダー

```http
Authorization: Bearer {your-token-here}
```

### トークンの取得

ログインエンドポイントでトークンを取得できます。トークンは有効期限なしで発行されます。

### 権限レベル

-   **user**: 一般ユーザー（自分の勤怠データのみアクセス可能）
-   **admin**: 管理者（全データアクセス可能、補正申請の承認・却下可能）

## 📝 エラーレスポンス

### バリデーションエラー (422 Unprocessable Entity)

```json
{
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password field is required."]
    }
}
```

### 認証エラー (401 Unauthorized)

```json
{
    "message": "Unauthenticated"
}
```

### 認可エラー (403 Forbidden)

```json
{
    "message": "This action is unauthorized"
}
```

### リソース未発見 (404 Not Found)

```json
{
    "message": "Resource not found"
}
```

### サーバーエラー (500 Internal Server Error)

```json
{
    "message": "Internal server error"
}
```

## 🛠 開発環境での API テスト

### curl を使用した例

```bash
# ログイン
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 勤怠データ取得（トークンが必要）
curl -X GET http://localhost:8000/api/attendance \
  -H "Authorization: Bearer 1|abc123..."

# 出勤打刻
curl -X POST http://localhost:8000/api/attendance/check-in \
  -H "Authorization: Bearer 1|abc123..."
```

### Postman Collection

プロジェクトルートに `postman_collection.json` ファイルがある場合は、Postman にインポートして使用できます。

## 📊 データベーススキーマ

### users テーブル

-   `id` (Primary Key)
-   `name` (ユーザー名)
-   `email` (メールアドレス、Unique)
-   `role` (ロール: user/admin)
-   `email_verified_at` (メール認証日時)
-   `password` (ハッシュ化パスワード)

### attendances テーブル

-   `id` (Primary Key)
-   `user_id` (外部キー)
-   `date` (勤務日)
-   `check_in` (出勤時刻)
-   `check_out` (退勤時刻)
-   `total_work_time` (総労働時間)

### attendance_rests テーブル

-   `id` (Primary Key)
-   `attendance_id` (外部キー)
-   `rest_start` (休憩開始時刻)
-   `rest_end` (休憩終了時刻)

### attendance_corrects テーブル

-   `id` (Primary Key)
-   `user_id` (外部キー)
-   `date` (対象日)
-   `check_in` (修正後出勤時刻)
-   `check_out` (修正後退勤時刻)
-   `reason` (申請理由)
-   `status` (ステータス: pending/approved/rejected)
-   `reject_reason` (却下理由)

### attendance_correct_rests テーブル

-   `id` (Primary Key)
-   `attendance_correct_id` (外部キー)
-   `rest_start` (休憩開始時刻)
-   `rest_end` (休憩終了時刻)

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Cubet Techno Labs](https://cubettech.com)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[Many](https://www.many.co.uk)**
-   **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
-   **[DevSquad](https://devsquad.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[OP.GG](https://op.gg)**
-   **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
-   **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
