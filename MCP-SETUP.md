# Supabase MCP サーバー設定ガイド

## 概要
このプロジェクトでは、Cursor IDEでSupabase MCP（Model Context Protocol）サーバーを使用できるように設定されています。

## 設定ファイル

### 現在の設定: `.cursor/mcp.json`
```json
{
  "mcpServers": {
    "supabase": {
      "command": "npx",
      "args": [
        "-y",
        "@supabase/mcp-server-supabase@latest",
        "--access-token",
        "sbp_e8153851e863ad8fd543a2d2b7550ac77d387a67",
        "--read-only"
      ],
      "env": {
        "NODE_ENV": "production"
      }
    }
  }
}
```

### 代替設定（環境変数使用）: `.cursor/mcp-env.json`
環境変数を使用する場合は、このファイルを `.cursor/mcp.json` にリネームして使用してください。

## 使用方法

1. **前提条件**
   - Node.js がインストールされていること
   - npm が利用可能であること

2. **設定の有効化**
   - Cursor IDE を再起動
   - MCP サーバーが自動的に開始されます

3. **機能**
   - Supabase データベースの読み取り専用アクセス
   - AI アシスタントからのデータベース操作

## トラブルシューティング

### MCPサーバーが起動しない場合
```bash
# 手動でMCPサーバーをテスト
npm run mcp:test
```

### 依存関係の問題
```bash
# npm キャッシュのクリア
npm cache clean --force

# MCPサーバーの最新版を強制インストール
npx -y @supabase/mcp-server-supabase@latest
```

## セキュリティ注意事項

- アクセストークンは読み取り専用に設定されています
- 本番環境では環境変数を使用することを推奨します
- アクセストークンをVCSにコミットしないよう注意してください

## サポート

設定に問題がある場合は、以下を確認してください：
1. Node.js のバージョン（推奨: 16以上）
2. npm のバージョン
3. インターネット接続
4. Supabase アクセストークンの有効性



