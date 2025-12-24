# ローカルテスト環境セットアップガイド

## 方法1: XAMPP（推奨・初心者向け）

### 1. XAMPPダウンロード・インストール
1. https://www.apachefriends.org/jp/index.html からWindows版をダウンロード
2. インストーラーを実行（デフォルト設定でOK）
3. XAMPPコントロールパネルを起動

### 2. プロジェクトの配置
1. `C:\xampp\htdocs\` フォルダに移動
2. 新しいフォルダ `katayama-cms` を作成
3. 現在のプロジェクトファイルをすべて `katayama-cms` フォルダにコピー

### 3. XAMPPでApache開始
1. XAMPPコントロールパネルで「Apache」の「Start」ボタンをクリック
2. 緑色になればOK

### 4. 管理画面にアクセス
- URL: `http://localhost/katayama-cms/admin/login.php`
- ID: `admin`
- パスワード: `password`

---

## 方法2: PHP単体インストール（軽量）

### 1. PHPダウンロード
1. https://windows.php.net/download/ から「Non Thread Safe」版をダウンロード
2. `C:\php` フォルダに解凍

### 2. 環境変数設定
1. 「システムのプロパティ」→「環境変数」
2. 「Path」に `C:\php` を追加

### 3. 開発サーバー起動
```cmd
cd D:\katayama-website
php -S localhost:8000
```

### 4. 管理画面にアクセス
- URL: `http://localhost:8000/admin/login.php`
- ID: `admin`
- パスワード: `password`

---

## テスト手順

### 1. ログイン確認
- 管理画面にアクセス
- `admin` / `password` でログイン

### 2. お知らせ管理テスト
- ダッシュボードから「お知らせ管理」をクリック
- 新規作成でテスト記事を追加
- 編集・削除機能を確認

### 3. 施工実績管理テスト
- ダッシュボードから「施工実績管理」をクリック
- 新規作成でテスト実績を追加
- 編集・削除機能を確認

### 4. フロントエンド確認
- メインサイト（`http://localhost:8000/index.html`）を表示
- お知らせセクションにCMSデータが反映されているか確認
- 施工実績セクションにCMSデータが反映されているか確認

---

## トラブルシューティング

### PHP関連エラー
- ファイル権限エラー → `cms/data/` フォルダの書き込み権限を確認
- セッションエラー → ブラウザのキャッシュをクリア

### アクセスエラー
- 404エラー → URLパスが正しいか確認
- 500エラー → PHPエラーログを確認

---

## セキュリティ注意事項

### 本番環境前の変更必須項目
1. **パスワード変更**: `cms/data/settings.json` の `password` ハッシュを変更
2. **ファイル権限**: `cms/data/` フォルダの書き込み権限を適切に設定
3. **HTTPS設定**: 本番環境では必ずHTTPSを使用




