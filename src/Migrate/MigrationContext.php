<?php
namespace ngyuki\DbMigrate\Migrate;

interface MigrationContext extends \ArrayAccess
{
    /**
     * SQL を実行する
     *
     * このメソッドは dry-run モードならスキップされるため
     * 呼び出し元で dry-run を判定して処理を分岐する必要はありません
     *
     * @param string $sql
     * @param array|null $params
     */
    public function exec($sql, array $params = null);

    /**
     * コンソールにログを出力する
     *
     * @param string $log
     */
    public function log($log);

    /**
     * コンソールにログを表示する
     *
     * このメソッドは verbose のときだけログを表示します
     *
     * @param string $log
     */
    public function verbose($log);

    /**
     * 実行モードが dry-run かどうかを返す
     *
     * 実行モードが dry-run なら true をそうではないなら false を返します
     *
     * `context->exec($sql)` やクロージャーの戻り値を使う分には dry-run を
     * 呼び出し元で判定する必要はありませんが、アプリケーション独自の処理を行うときは
     * dry-run 実行時もクロージャーは呼び出されるため、このメソッドの用いて
     * 実行モードを判定して処理を分岐してください
     *
     * @return bool
     */
    public function isDryRun();

    /**
     * 設定ファイルが返した連想配列をそのまま返す
     *
     * @return array
     */
    public function getConfig();
}
