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

`sql/db-migrate.config.php` に設定ファイルを作ります。

```php
<?php
$pdo = new \PDO('mysql:dbname=test;host=localhost;charset=utf8', 'user', 'pass');

return array(
    // PDO のインスタンス
    'pdo' => $pdo,
    // マイグレーションスクリプトを配置するディレクトリ（設定ファイルからの相対）
    'directory' => 'migrate',
);
```

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
up: 20140829-01.sql
up: 20140829-02.sql
up: 20140830-01.php
up: 20140830-02.sql
```

`-n` オプションを付けると実際には実行しません (dry run)。

`-v` オプションを付けると実行した SQL が一緒に表示されます。

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
$ vendor/bin/db-migrate migrate 20140829
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
* 20140828-01.sql
* 20140829-01.sql
* 20140829-02.sql
  20140830-01.php
  20140830-02.sql

$ vendor/bin/db-migrate down
down: 20140829-02.sql
```

引数として `--all` を付けるとすべての適用済のバージョンがロールバックされます。

```console
$ vendor/bin/db-migrate status
* 20140828-01.sql
* 20140829-01.sql
* 20140829-02.sql
  20140830-01.php
  20140830-02.sql

$ vendor/bin/db-migrate down --all
down: 20140829-02.sql
down: 20140829-01.sql
down: 20140828-01.sql
```

引数として `--missing` を付けるとマイグレーションファイルが失われているバージョンのみロールバックされます。

```console
$ vendor/bin/db-migrate status
* 20140828-01.sql
* 20140829-01.sql (missing)
* 20140829-02.sql (missing)
  20140830-01.php
  20140830-02.sql

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
```

オプション `-c` で指定されたパスがディレクトリの場合、そのディレクトリから次の順で設定ファイルが探索されます。

 - `db-migrate.config.php`
 - `db-migrate.config.php.dist`

## アンドキュメンテッド

- Configure
  - Configure::register で設定ファイルの内容を返すクロージャーを仕込めば設定ファイルレスにできる
  - composer autoload-dev.files から実行するスクリプトで Configure::register する想定
