# テスト戦略の整理

## 1. モックテスト（ItemPurchaseFeatureTest.php）

-   **目的**: アプリケーションロジックのテスト
-   **対象**: StripeService のモック
-   **メリット**: 高速、安定、外部依存なし
-   **用途**: 日常的なテスト実行

## 2. 統合テスト（ItemPurchaseIntegrationTest.php）

-   **目的**: 実際の Stripe API との連携テスト
-   **対象**: 実際の Stripe API
-   **メリット**: 本番環境での動作保証
-   **用途**: リリース前の最終確認

## 3. 本番環境での安全性

-   **StripeService**: 実際の API を呼び出す
-   **例外処理**: 適切に実装済み
-   **エラーハンドリング**: 実装済み
-   **ログ出力**: デバッグ情報を出力

## 4. 推奨テスト実行方法

```bash
# 日常的なテスト（モック使用）
./vendor/bin/phpunit tests/Feature/ItemPurchaseFeatureTest.php

# リリース前の統合テスト（実際のAPI使用）
./vendor/bin/phpunit tests/Feature/ItemPurchaseIntegrationTest.php

# 全テスト実行
./vendor/bin/phpunit
```
