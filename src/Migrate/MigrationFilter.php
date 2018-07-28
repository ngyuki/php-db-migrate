<?php
namespace ngyuki\DbMigrate\Migrate;

class MigrationFilter
{
    /**
     * 指定されたバージョンまでマイグレートするように仕分ける
     *
     * @param Status[] $migrations
     * @param string $target
     *
     * @return array [$up_migrations, $down_migrations]
     */
    public function migrate(array $migrations, $target)
    {
        $up = array();
        $down = array();

        foreach ($migrations as $version => $migration) {
            if ($target === null) {
                // 未指定なら常に UP する
                $cmp = -1;
            } else {
                $cmp = strcmp($version, $target);
            }

            if ($migration->isMissing()) {
                // ファイルが見つからなければ DOWN する
                $cmp = 1;
            }

            if ($cmp <= 0) {
                if (!$migration->isApplied()) {
                    $up[$version] = $migration;
                }
            } else {
                if ($migration->isApplied()) {
                    $down[$version] = $migration;
                }
            }
        }

        return [$up, $down];
    }

    /**
     * @param Status[] $migrations
     * @param bool $missing
     * @param bool $all
     *
     * @return Status[]
     */
    public function down(array $migrations, $missing, $all)
    {
        $down = array();

        foreach ($migrations as $version => $migration) {
            if ($migration->isApplied()) {
                if ($missing) {
                    if ($migration->isMissing()) {
                        $down[$version] = $migration;
                    }
                } else {
                    if (!$all) {
                        $down = [];
                    }
                    $down[$version] = $migration;
                }
            }
        }

        return $down;
    }
}
