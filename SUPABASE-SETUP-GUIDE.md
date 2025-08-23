# Supabase統合セットアップガイド

## 概要

このガイドでは、片山建設工業のウェブサイトをSupabaseデータベースと統合して動的サイトにする手順を説明します。

## 前提条件

- Supabaseアカウント (既に設定済み)
- Supabaseプロジェクト: `katayama-dx` (Project ID: `iygiuutslpnvrheqbqgv`)
- PHPが動作するWebサーバー環境

## セットアップ手順

### 1. データベーステーブルの作成

SupabaseダッシュボードのSQL Editor、またはpsqlクライアントで以下のマイグレーションを実行してください：

```bash
# 1. テーブル作成
psql -h db.iygiuutslpnvrheqbqgv.supabase.co -U postgres -d postgres -f database-migrations/001_create_website_tables.sql

# 2. 初期データ投入
psql -h db.iygiuutslpnvrheqbqgv.supabase.co -U postgres -d postgres -f database-migrations/002_seed_initial_data.sql
```

### 2. Supabase APIキーの設定

1. Supabaseダッシュボードにアクセス
2. 「Settings」→「API」でAPIキーを確認
3. `config/supabase.php`ファイルを編集：

```php
// 実際のキーに置き換えてください
public const ANON_KEY = 'YOUR_ACTUAL_ANON_KEY_HERE';
public const SERVICE_ROLE_KEY = 'YOUR_ACTUAL_SERVICE_ROLE_KEY_HERE';
```

### 3. ファイル構成の確認

以下のファイルが正しく配置されていることを確認してください：

```
katayama-website/
├── config/
│   └── supabase.php              # Supabase設定
├── lib/
│   └── SupabaseClient.php        # Supabaseクライアント
├── api/
│   ├── supabase-news.php         # ニュースAPI
│   ├── supabase-works.php        # 施工実績API
│   ├── supabase-services.php     # サービスAPI
│   ├── supabase-testimonials.php # お客様の声API
│   └── supabase-stats.php        # 会社統計API
├── assets/js/
│   └── supabase-integration.js   # フロントエンド統合
├── admin/pages/
│   └── supabase-news.php         # 管理画面
└── database-migrations/
    ├── 001_create_website_tables.sql
    └── 002_seed_initial_data.sql
```

### 4. Row Level Security (RLS) の確認

テーブルにRLSポリシーが適用されていることを確認：

```sql
-- 公開データの読み取り許可を確認
SELECT tablename, policyname, cmd, roles
FROM pg_policies 
WHERE schemaname = 'public' 
AND tablename IN ('news', 'works', 'services', 'testimonials', 'company_stats', 'partners');
```

### 5. 動作テスト

#### API エンドポイントのテスト

```bash
# ニュースAPI
curl "https://yourdomain.com/api/supabase-news.php?limit=5"

# 施工実績API
curl "https://yourdomain.com/api/supabase-works.php?category=Residential"

# サービスAPI
curl "https://yourdomain.com/api/supabase-services.php"
```

#### フロントエンドの動作確認

1. ブラウザでindex.htmlにアクセス
2. Developer Toolsのコンソールでエラーがないことを確認
3. ニュース、お客様の声、統計データが動的に読み込まれることを確認

### 6. 管理画面の設定

1. `admin/pages/supabase-news.php`にアクセス
2. 管理者でログイン
3. データの作成・編集・削除が正常に動作することを確認

## データベーススキーマ

### 作成されるテーブル

| テーブル名 | 説明 | 主要カラム |
|-----------|------|-----------|
| `news` | ニュース・お知らせ | title, content, category, published_date, status |
| `works` | 施工実績 | title, description, category, featured_image, completion_date |
| `services` | サービス内容 | title, description, features, sort_order |
| `testimonials` | お客様の声 | customer_name, content, project_type, rating |
| `company_stats` | 会社統計 | stat_name, stat_value, stat_unit |
| `partners` | パートナー企業 | company_name, logo_image, website_url |
| `site_settings` | サイト設定 | setting_key, setting_value |

### データの状態管理

- **news/works**: `draft` → `published` → `archived`
- **その他**: `active` → `inactive`

## APIエンドポイント

| エンドポイント | メソッド | 説明 | パラメータ |
|---------------|---------|------|-----------|
| `/api/supabase-news.php` | GET | ニュース一覧取得 | limit, offset, category |
| `/api/supabase-works.php` | GET | 施工実績一覧取得 | limit, offset, category |
| `/api/supabase-services.php` | GET | サービス一覧取得 | - |
| `/api/supabase-testimonials.php` | GET | お客様の声取得 | limit |
| `/api/supabase-stats.php` | GET | 会社統計取得 | - |

## トラブルシューティング

### 1. データが表示されない

**原因**: APIキーの設定が間違っている
**解決**: `config/supabase.php`のAPIキーを確認

**原因**: RLSポリシーでブロックされている
**解決**: Supabaseダッシュボードでポリシー設定を確認

### 2. CORSエラーが発生する

**原因**: ブラウザのCORSポリシー
**解決**: ローカル開発時はWebサーバー経由でアクセス

### 3. データベース接続エラー

**原因**: ネットワークまたは認証の問題
**解決**: 
- ファイアウォール設定を確認
- Supabaseプロジェクトの状態を確認
- APIキーの有効性を確認

### 4. SSL証明書エラー

**原因**: cURLのSSL検証
**解決**: `SupabaseClient.php`で`CURLOPT_SSL_VERIFYPEER`を確認

## パフォーマンス最適化

### 1. キャッシュ戦略

- フロントエンドで5分間のメモリキャッシュを実装済み
- サーバーサイドキャッシュ（Redis等）の導入を検討

### 2. データベースインデックス

```sql
-- 必要に応じて追加のインデックスを作成
CREATE INDEX idx_news_published_date ON news(published_date) WHERE status = 'published';
CREATE INDEX idx_works_completion_date ON works(completion_date) WHERE status = 'published';
CREATE INDEX idx_services_sort_order ON services(sort_order) WHERE status = 'active';
```

### 3. 画像最適化

- 施工実績画像の圧縮
- WebP形式での配信
- CDN（Supabase Storage）の活用

## セキュリティ考慮事項

### 1. APIキーの管理

- 環境変数での管理を推奨
- Service Role Keyは管理機能でのみ使用
- 定期的なキーローテーション

### 2. 入力値検証

- すべてのユーザー入力をサニタイズ
- SQLインジェクション対策（Supabase側で実装済み）
- XSS対策のためのHTMLエスケープ

### 3. 認証・認可

- 管理画面への適切なアクセス制御
- RLSポリシーによるデータアクセス制御

## 今後の拡張予定

1. **管理画面の完全実装**
   - CRUD操作の完成
   - ファイルアップロード機能
   - バルク操作

2. **検索機能の追加**
   - 全文検索
   - フィルタリング機能
   - タグベース検索

3. **多言語対応**
   - 国際化テーブル設計
   - 言語切り替え機能

4. **分析機能**
   - アクセス統計
   - コンテンツパフォーマンス分析

## サポート

質問や問題が発生した場合は、以下の情報を含めてお問い合わせください：

- エラーメッセージ
- 実行したSQL文やAPI呼び出し
- ブラウザの開発者ツールのログ
- 環境情報（PHP、Webサーバーのバージョン等）


