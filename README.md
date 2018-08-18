# db migrate

[![Build Status](https://travis-ci.org/ngyuki/php-db-migrate.svg?branch=master)](https://travis-ci.org/ngyuki/php-db-migrate)
[![Coverage Status](https://coveralls.io/repos/github/ngyuki/php-db-migrate/badge.svg)](https://coveralls.io/github/ngyuki/php-db-migrate)
[![Latest Stable Version](https://poser.pugx.org/ngyuki/db-migrate/version)](https://packagist.org/packages/ngyuki/db-migrate)
[![Latest Unstable Version](https://poser.pugx.org/ngyuki/db-migrate/v/unstable)](//packagist.org/packages/ngyuki/db-migrate)
[![License](https://poser.pugx.org/ngyuki/db-migrate/license)](https://packagist.org/packages/ngyuki/db-migrate)

## 使い方

### インストール

composer でインストールします。

```console
$ composer require ngyuki/db-migrate
```

### 設定ファイル

`db-migrate.php` に設定ファイルを作ります。

```php
<?php
// db-migrate.php
$pdo = new new PDO('mysql:dbname=test;host=localhost;charset=utf8', 'user', 'pass', array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
));
return array(
    // PDO のインスタンス
    'pdo' => $pdo,
    // マイグレーションスクリプトを配置するディレクトリ (default: migration)
    // 相対パスは設定フィアルからの相対として解釈
    'directory' => __DIR__ . '/sql/migration',
    // マイグレーション管理テーブルのテーブル名 (default: migration)
    'table' => 'migration',
);
```

### マイグレーションスクリプト

マイグレーションスクリプトを作成します。

```console
$ vim sql/migration/20140827-01.sql
```

スクリプトは SQL で記述します。

```sql
create table tt (
  id int not null primary key
);
```

### 実行

migrate サブコマンドでマイグレーションを実行します。

```console
$ vendor/bin/db-migrate migrate -v
up: 20140827-01.sql
        create table tt (
          id int not null primary key
        );
```

status サブコマンドでマイグレーションのステータスを表示します。先頭の `*` とマークされたスクリプトが適用済です。未適用ならマークは表示されません。

```console
$ vendor/bin/db-migrate status
[*] 20140827-01.sql
[*] 20140827-02.sql
[ ] 20140828-01.sql
```

## コマンド

### `db-migrate status`

マイグレーションのステータスを表示します。適用済のバージョンは `*` でマークされます。

```console
$ vendor/bin/db-migrate status
[*] 20140827-01.sql
[*] 20140827-02.sql
[ ] 20140828-01.sql
```

ファイルが失われている適用済バージョンは missing が表示されます。

```console
$ vendor/bin/db-migrate status
[*] 20140827-01.sql
[*] 20140827-02.sql (missing)
[ ] 20140828-01.sql
```

すべてのバージョンが適用済で、ファイルが失われてもいなければ終了コードは 0 になります。

```console
$ vendor/bin/db-migrate status ; echo -e "\nexit code: $?"
[*] 20140827-01.sql
[*] 20140827-02.sql
[*] 20140828-01.sql

exit code: 0
```

未適用のバージョンがあるか、あるいは適用済でも失われたファイルがある場合は終了コードは 1 になります。


```console
$ vendor/bin/db-migrate status ; echo -e "\nexit code: $?"
[*] 20140827-01.sql
[*] 20140827-02.sql (missing)
[ ] 20140828-01.sql

exit code: 1
```

### `db-migrate migrate`

マイグレーションを実行します。

```console
$ vendor/bin/db-migrate migrate
up: 20140828-01.sql
up: 20140829-01.sql
up: 20140829-02.sql
up: 20140830-01.php
up: 20140830-02.sql
```

`-n` オプションを付けると実際には実行しません (dry run)。

`-v` オプションを付けると実行した SQL が一緒に表示されます。

ファイルが失われている適用済バージョンは down されます。

```console
$ vendor/bin/db-migrate status
[*] 20140828-01.sql
[*] 20140829-01.sql (missing)
[ ] 20140829-02.sql

$ vendor/bin/db-migrate migrate
down: 20140829-01.sql
up: 20140829-02.sql
```

マイグレーションのファイル名を指定すると、指定したバージョンまでマイグレーションが実行されます。

```console
$ vendor/bin/db-migrate migrate 20140829-01.sql
up: 20140828-01.sql
up: 20140829-01.sql
```

指定したファイルとは文字列として比較されるため、存在しないファイル名を指定することもできます。

```console
$ vendor/bin/db-migrate migrate 20140830
up: 20140828-01.sql
up: 20140829-01.sql
up: 20140829-02.sql
```

バージョンは戻すこともできます。

```console
$ vendor/bin/db-migrate migrate 20140829
down: 20140829-02.sql
down: 20140829-01.sql
```

例えば `0` を指定すればすべてのバージョンが戻されます。

```console
$ vendor/bin/db-migrate migrate 0
down: 20140829-02.sql
down: 20140829-01.sql
down: 20140828-01.sql
```

### `db-migrate up`

未適用のバージョンを1つだけマイグレーションします。

```console
$ vendor/bin/db-migrate up
up: 20140829-02.sql
```

引数として `--all` を付けるとすべての未適用のバージョンがマイグレーションされます。

```console
$ vendor/bin/db-migrate up --all
up: 20140829-02.sql
up: 20140830-01.php
up: 20140830-02.sql
```

### `db-migrate down`

適用済のバージョンを1つだけロールバックします。

```console
$ vendor/bin/db-migrate status
[*] 20140828-01.sql
[*] 20140829-01.sql
[*] 20140829-02.sql
[ ] 20140830-01.php
[ ] 20140830-02.sql

$ vendor/bin/db-migrate down
down: 20140829-02.sql
```

引数として `--all` を付けるとすべての適用済のバージョンがロールバックされます。

```console
$ vendor/bin/db-migrate status
[*] 20140828-01.sql
[*] 20140829-01.sql
[*] 20140829-02.sql
[ ] 20140830-01.php
[ ] 20140830-02.sql

$ vendor/bin/db-migrate down --all
down: 20140829-02.sql
down: 20140829-01.sql
down: 20140828-01.sql
```

引数として `--missing` を付けるとマイグレーションファイルが失われているバージョンのみロールバックされます。

```console
$ vendor/bin/db-migrate status
[*] 20140828-01.sql
[*] 20140829-01.sql (missing)
[*] 20140829-02.sql (missing)
[ ] 20140830-01.php
[ ] 20140830-02.sql

$ vendor/bin/db-migrate down --missing
down: 20140829-02.sql
down: 20140829-01.sql
```

### `db-migrate redo`

down -> up を連続して実行します。

```console
$ vendor/bin/db-migrate redo
down: 20140829-02.sql
up: 20140829-02.sql
```

### `db-migrate mark`

マイグレーションが適用済であるとマークします。引数としてスクリプトのファイル名を指定します。

```console
$ vendor/bin/db-migrate mark 20140828-01.sql
mark: 20140828-01.sql
```

なんらかの原因でマイグレーションが失敗したときに、手作業でマイグレーションを行った後に失敗したスクリプトを適用済であるとマークするために使用できます。

引数として `--all` を付けるとすべてのスクリプトが適用済であるとマークされます。

```console
$ vendor/bin/db-migrate mark --all
mark: 20140828-01.sql
mark: 20140829-01.sql
mark: 20140829-02.sql
mark: 20140830-01.php
mark: 20140830-02.sql
```

### `db-migrate unmark`

マイグレーションが未適用であるとマークします。引数としてスクリプトのファイル名を指定します。

```console
$ vendor/bin/db-migrate unmark 20140828-01.sql
unmark: 20140828-01.sql
```

引数として `--all` を付けるとすべてのスクリプトが未適用であるとマークされます。

```console
$ vendor/bin/db-migrate unmark --all
unmark: 20140828-01.sql
unmark: 20140829-01.sql
unmark: 20140829-02.sql
unmark: 20140830-01.php
unmark: 20140830-02.sql
```

### `db-migrate exec`

指定されたディレクトリのスクリプトを単純に実行します。ディレクトリはカレントディレクトリからの相対パス、または絶対パスで指定してください。

```console
$ vendor/bin/db-migrate exec sql/routine/
exec: 001-view.php.sql
```

ビューなどは、マイグレーションでバージョン管理するよりも毎回作りなおしたほうが簡単です。このコマンドはそのようなスクリプトを実行するために利用できます。

### `db-migrate clear`

データベースを作り直します。

```console
$ vendor/bin/db-migrate clear
clear database
```

データベースの中のすべてのテーブルなどが削除されます。マイグレーションが中途半端に失敗して如何ともし難い状況に陥ったときの最終手段として使用してください。

なお、確認用プロンプトとかは表示されないので、十分注意してください。

## マイグレーションスクリプト

マイグレーションスクリプトは次のいずれかの形式で作成できます。形式は拡張子で判断されます。

 - SQL
    - 拡張子 `.sql`
 - PHP
    - 拡張子 `.php`

いずれの形式でも実行時のカレントディレクトリは設定ファイルのあるディレクトリです。`LOAD DATA LOCAL INFILE` などで他のファイルを参照する場合、設定ファイルのあるディレクトリからの相対パスで記述する必要があります。

### SQL

次のような形式で記述します。

```sql
create table tt (
  id int not null primary key
);

/* {{ down }}

drop table if exists tt;

/**/
```

`{{ down }}` が含まれている行でマイグレーションの UP と DOWN を区切ります。この例では DOWM 全体がコメントになるように記述していますが、次のように記述しても同じです。

```sql
create table tt (
  id int not null primary key
);

{{ down }}

drop table if exists tt;
```

SQL はセミコロンでコマンドが区切られているものとして解釈します。ブロックコメント `/* ... */` の中のセミコロンは無視しますが、シングルラインコメント `-- ...` や文字列リテラルにセミコロンを記述すると誤解釈します。また、ストアドプロシージャなどの内部のセミコロンでも誤解釈します。

### PHP

2つのクロージャーの配列を返す PHP スクリプトを記述します。1つ目のクロージャーで `up` の処理、2つ目のクロージャーで `down` の処理を実行します。

```php
<?php
use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (MigrationContext $context) {
        // up の処理
        $context->exec("create table tt ( id int not null primary key )");
    },
    function (MigrationContext $context) {
        // down の処理
        $context->exec("drop table tt");
    },
);
```

クロージャーの引数には `ngyuki\DbMigrate\Migrate\MigrationContext` のインスタンスが渡されます。このインスタンスでメソッドやプロパティを利用できます。

```php
/**
 * 引数で指定された SQL を実行します
 * dry-run モードでは自動的にスキップされるため呼び出し元で dry-run を判断して分岐する必要はありません
 */
$context->exec($sql);

/**
 * コンソールにログを出力します
 */
$context->log($log);

/**
 * コンソールにログを出力します
 * このログは verbose モードで実行されているときだけ表示されます
 */
$context->verbose($log);

/**
 * 実行モードが dry-run なら true を、そうではないなら false を返します
 * `context->exec($sql)` を使っていれば dry-run を呼び出し元で判断する必要はありませんが
 * アプリケーション独自の処理を行うときは dry-run 実行時もクロージャーは呼び出されるため
 * このプロパティの値を見て処理を分岐してください
 */
$context->dryRun;

/**
 * 設定ファイルが返した連想配列をそのまま得ます
 */
$context->config;
```

また `MigrationContext` とは別に、設定ファイルが返した連想配列の要素がクロージャーの引数に渡されます。
引数の順番には特に意味はなく、引数は型宣言および引数名によって自動的にインジェクションされます。

例えば設定ファイルで次のように記述していた場合、

```php
use App\HogeClass;

return [
    'pdo' => new \PDO(/* ... */),
    'value' => 12345,
    HogeClass::class => $hogeClass,
];
```

クロージャーは次のように記述できます。

```php
<?php
use App\HogeClass;
use ngyuki\DbMigrate\Migrate\MigrationContext;

return array(
    function (HogeClass $hoge, MigrationContext $context, $value) {
        // ...
    },
    function ($value, HogeClass $hoge, MigrationContext $context) {
        // ...
    },
);
```

## 設定ファイル

設定ファイルは次の方法で指定できます。上にあるものが優先されます。

- コマンドラインオプション `-c` で指定
- 環境変数 `PHP_DB_MIGRATE_CONFIG` で指定
- composer.json で指定
- コールバックを登録
- カレントディレクトリから探す

### コマンドラインオプション `-c` で指定

コマンドラインオプション `-c` で設定ファイルを指定できます。

```console
$ vendor/bin/db-migrate migrate -c config.php
```

### 環境変数 `PHP_DB_MIGRATE_CONFIG` で指定

環境変数 `PHP_DB_MIGRATE_CONFIG` で指定できます。

```console
PHP_DB_MIGRATE_CONFIG=config.php vendor/bin/db-migrate migrate
```

### composer.json で指定

プロジェクトの `composer.json` に設定ファイルのパスを指定できます。`composer.json` かカレントディレクトリから親ディレクトリを遡って検索されます。

`composer.json` には次のような形で設定ファイル名を指定します。ファイル名は `composer.json` ファイルからの相対で指定します。

```json
{
    "extra": {
        "db-migrate": "config.php"
    }
}
```

配列で指定することも可能で、最初に見つかったファイルが使用されます。

```json
{
    "extra": {
        "db-migrate": ["config.php", "config.php.dist"]
    }
}
```

### カレントディレクトリから探す

カレントディレクトリから次の順で探索され、最初に見つかったものが使用されます。

- `db-migrate.php`
- `db-migrate.php.dist`
