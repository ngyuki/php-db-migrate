# db migrate

## 使い方

### インストール

composer でインストールします。

```
composer require ngyuki/db-migrate:dev-master
```

### 設定ファイル

`sql/db-migrate.config.php` に設定ファイルを作ります。

```php
<?php
$pdo = new \PDO('mysql:dbname=test;host=localhost;charset=utf8', 'user', 'pass');

return array(
    'pdo' => $pdo,
    'directory' => 'migrate',
);
```

`pdo` には PDO のインスタンスを、`directory` にはマイグレーションスクリプトを配置するディレクトリを設定ファイルからの相対パスで指定します。

### マイグレーションスクリプト

マイグレーションスクリプトを作成します。

```
vim sql/migrate/20140827-01.sql
```

スクリプトは SQL で記述します。

```sql
create table tt (
  id int not null primary key
);
```

### 実行

migrate サブコマンドでマイグレーションを実行します。

```
$ vendor/bin/db-migrate migrate -v
migrate: 20140827-01.sql
        create table tt (
          id int not null primary key
        );
fix version: 20140827-01.sql
```

status サブコマンドでマイグレーションのステータスを表示します。先頭の `*` とマークされたスクリプトが適用済です。未適用ならマークは表示されません。

```
$ vendor/bin/db-migrate status
* 20140827-01.sql
```

## コマンド

### `db-migrate status`

マイグレーションのステータスを表示します。適用済のスクリプトは `*` でマークされます。

```
$ vendor/bin/db-migrate status
* 20140827-01.sql
* 20140827-02.sql
  20140828-01.sql
```

すべてのスクリプトが適用済の場合は終了コードが 0 になります。未適用のスクリプトがあれば終了コードは 1 になります。

```
$ vendor/bin/db-migrate status ; echo "exit code: $?"
* 20140827-01.sql
* 20140827-02.sql
  20140828-01.sql
exit code: 1
```

### `db-migrate migrate`

マイグレーションを実行します。

```
$ vendor/bin/db-migrate migrate"
migrate: 20140828-01.sql
fix version: 20140828-01.sql
```

`-n` オプションを付けると実際には実行しません (dryrun)

`-v` オプションを付けると詳細なログを出力します。

### `db-migrate fix`

マイグレーションスクリプトが適用済であるとマークします。引数としてスクリプトのファイル名を指定します。

```
$ vendor/bin/db-migrate fix 20140828-01.sql
fix version: 20140828-01.sql
```

なんらかの原因でマイグレーションが失敗したときに、手作業でマイグレーションを行った後に失敗したスクリプトを適用済であるとマークするために使用できます。

引数として `--all` を付けるとすべてのスクリプトが適用済であるとマークされます。

```
$ vendor/bin/db-migrate fix --all
fix version: 20140828-01.sql
fix version: 20140828-02.sql
```

引数として `--clear` を付けるとすべてのスクリプトが未適用であるとマークします（適用済のマークを削除します）。

```
$ vendor/bin/db-migrate fix --clear
clear all version
```

### `db-migrate exec`

指定されたディレクトリのスクリプトを単純に実行します。ディレクトリはカレントディレクトリからの相対パス、または絶対パスで指定してください。

```
$ vendor/bin/db-migrate exec sql/routine/
migrate: 001-view.php.sql
migrate: 002-procedure.php.sql
migrate: 003-trigger.php.sql
```

ビューやストアドプロシージャは、マイグレーションでバージョン管理するよりも毎回作りなおしたほうが簡単です。

このコマンドはそのようなスクリプトを簡単に実行するために利用できます。

## マイグレーションスクリプト

マイグレーションスクリプトは次のいずれかの形式で作成できます。形式は拡張子で区別されます。

 - SQL
    - 拡張子 `.sql`
 - PHP
    - 拡張子 `.php`
 - SQL+PHP
    - 拡張子 `.sql.php`
    - 拡張子 `.php.sql`

いずれの形式でも実行時のカレントディレクトリは設定ファイルのあるディレクトリになります。

`LOAD DATA LOCAL INFILE` などで他のファイルを参照する場合、設定ファイルのあるディレクトリからの相対パスで記述する必要があります。

### SQL

ただの SQL ファイルです。

セミコロンでコマンドが区切られているものとして解釈します。

ブロックコメント `/* ... */` の中のセミコロンは無視しますが、シングルラインコメント `-- ...` や文字列リテラルにセミコロンを記述すると誤解釈します。また、ストアドプロシージャなどの内部のセミコロンでも誤解釈します。

そのようなケースでは後述の SQL+PHP 形式を使用してください。

### PHP

PHP として実行されます。

コンフィグの `extra` に指定した連想配列が `extract` されます。

例えば、次のような設定ファイルなら、

```php
<?php
$pdo = new \PDO('mysql:dbname=test;host=localhost;charset=utf8', 'user', 'pass');

return array(
    'pdo' => $pdo,
    'directory' => 'migrate',
    'extra' => array(
        'pdo' => $pdo,
        'val' => 12345,
    ),
);
```

`$pdo` と `$val` をスクリプトから参照することができます。

```php
<?php
$stmt = $pdo->prepare('insert into tt values (?)');
$stmt->bindValue(1, PDO::PARAM_INT);
$stmt->execute($val);
```

### SQL+PHP

スクリプトが PHP として実行された後、その出力が SQL として解釈されて実行されます。

PHP 形式の場合と同じく、コンフィグの `extra` に指定した連想配列が `extract` されます。

また、スクリプトで `$this->delimiter('//')` などと記述すると、SQL のデミリタを変更することができます。

例えば、ストアドプロシージャを定義するスクリプトは次のように書くことができます。

```sql
/* <?php $this->delimiter('//') ?> */
/* <?php if(false): ?> */
delimiter //
/* <?php endif; ?> */

DROP PROCEDURE IF EXISTS sp_sample;
CREATE PROCEDURE sp_sample ()
BEGIN
  SELECT 1 as one FROM DUAL;
END
//

/* <?php if(false): ?> */
delimiter ;
/* <?php endif; ?> */
```

## 設定ファイル

設定ファイルはカレントディレクトリから次の順で探索され、最初に見つかったものが使用されます。

 - `sql/db-migrate.config.php`
 - `sql/db-migrate.config.php.dist`
 - `db-migrate.config.php`
 - `db-migrate.config.php.dist`

オプション `-c` で設定ファイルを指定することもできます。

```
$ vendor/bin/db-migrate migrate -c sql/config.php"
migrate: 20140828-01.sql
fix version: 20140828-01.sql
```

オプション `-c` で指定されたパスがディレクトリの場合、そのディレクトリから次の順で設定ファイルが探索されます。

 - `db-migrate.config.php`
 - `db-migrate.config.php.dist`
