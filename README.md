# db migrate

## 使い方

### インストール

composer でインストールします。

```console
$ composer require ngyuki/db-migrate:dev-master
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

```console
$ vim sql/migrate/20140827-01.sql
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
* 20140827-01.sql
```

## コマンド

### `db-migrate status`

マイグレーションのステータスを表示します。適用済のスクリプトは `*` でマークされます。

```console
$ vendor/bin/db-migrate status
* 20140827-01.sql
* 20140827-02.sql
  20140828-01.sql
```

すべてのスクリプトが適用済の場合は終了コードが 0 になります。未適用のスクリプトがあれば終了コードは 1 になります。

```console
$ vendor/bin/db-migrate status ; echo "exit code: $?"
* 20140827-01.sql
* 20140827-02.sql
  20140828-01.sql
exit code: 1
```

### `db-migrate migrate`

マイグレーションを実行します。

```console
$ vendor/bin/db-migrate migrate
up: 20140828-01.sql
```

`-n` オプションを付けると実際には実行しません (dry run)。

`-v` オプションを付けると実行した SQL が一緒に表示されます。

### `db-migrate set`

マイグレーションが適用済であるとマークします。引数としてスクリプトのファイル名を指定します。

```console
$ vendor/bin/db-migrate set 20140828-01.sql
set version: 20140828-01.sql
```

なんらかの原因でマイグレーションが失敗したときに、手作業でマイグレーションを行った後に失敗したスクリプトを適用済であるとマークするために使用できます。

引数として `--all` を付けるとすべてのスクリプトが適用済であるとマークされます。

```console
$ vendor/bin/db-migrate set --all
set version: 20140828-01.sql
set version: 20140828-02.sql
```

### `db-migrate unset`

マイグレーションが未適用であるとマークします。引数としてスクリプトのファイル名を指定します。

```console
$ vendor/bin/db-migrate unset 20140828-01.sql
unset version: 20140828-01.sql
```

引数として `--all` を付けるとすべてのスクリプトが未適用であるとマークされます。

```console
$ vendor/bin/db-migrate unset --all
unset version: 20140828-01.sql
unset version: 20140828-02.sql
```

### `db-migrate exec`

指定されたディレクトリのスクリプトを単純に実行します。ディレクトリはカレントディレクトリからの相対パス、または絶対パスで指定してください。

```console
$ vendor/bin/db-migrate exec sql/routine/
exec: 001-view.php.sql
exec: 002-procedure.php.sql
exec: 003-trigger.php.sql
```

ビューやストアドプロシージャは、マイグレーションでバージョン管理するよりも毎回作りなおしたほうが簡単です。このコマンドはそのようなスクリプトを実行するために利用できます。

## マイグレーションスクリプト

マイグレーションスクリプトは次のいずれかの形式で作成できます。形式は拡張子で判断されます。

 - SQL
    - 拡張子 `.sql`
 - PHP
    - 拡張子 `.php`

いずれの形式でも実行時のカレントディレクトリは設定ファイルのあるディレクトリになります。

`LOAD DATA LOCAL INFILE` などで他のファイルを参照する場合、設定ファイルのあるディレクトリからの相対パスで記述する必要があります。

### SQL

SQL ファイルです。次のような形式で記述します。

```sql
create table tt (
  id int not null primary key
);

/* {{ down }}

drop table if exists tt;

/**/
```

`{{ down }}` が含まれている行で、マイグレーションの UP と DOWN を区切っています。この例では DOWM 全体がコメントになるように記述していますが、次のように記述しても同じです。

```sql
create table tt (
  id int not null primary key
);

{{ down }}

drop table if exists tt;
```

SQL はセミコロンでコマンドが区切られているものとして解釈します。ブロックコメント `/* ... */` の中のセミコロンは無視しますが、シングルラインコメント `-- ...` や文字列リテラルにセミコロンを記述すると誤解釈します。また、ストアドプロシージャなどの内部のセミコロンでも誤解釈します。

### PHP

**Experimental**

仕様がぶれっぶれなので使わないでください。

## 設定ファイル

設定ファイルはカレントディレクトリから次の順で探索され、最初に見つかったものが使用されます。

 - `sql/db-migrate.config.php`
 - `sql/db-migrate.config.php.dist`
 - `db-migrate.config.php`
 - `db-migrate.config.php.dist`

オプション `-c` で設定ファイルを指定することもできます。

```console
$ vendor/bin/db-migrate migrate -c sql/config.php
migrate: 20140828-01.sql
fix version: 20140828-01.sql
```

オプション `-c` で指定されたパスがディレクトリの場合、そのディレクトリから次の順で設定ファイルが探索されます。

 - `db-migrate.config.php`
 - `db-migrate.config.php.dist`
