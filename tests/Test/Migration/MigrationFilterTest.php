<?php
namespace Test\Migrate;

use ngyuki\DbMigrate\Migrate\MigrationFilter;
use ngyuki\DbMigrate\Migrate\Migration;
use TestHelper\TestCase;

class MigrationFilterTest extends TestCase
{
    /**
     * @test
     */
    public function migrate_nothing()
    {
        $migrations = [
            '1000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '4000.sql' => (new Migration())->setScript('x')->setApplied(true),
        ];

        list ($up, $down) = (new MigrationFilter())->migrate($migrations, null);

        assertEmpty($up);
        assertEmpty($down);
    }

    /**
     * @test
     */
    public function migrate_()
    {
        $migrations = [
            '1000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '4000.sql' => (new Migration())->setScript('x')->setApplied(false),
        ];

        list ($up, $down) = (new MigrationFilter())->migrate($migrations, null);

        assertThat(array_keys($up), equalTo([
            '1000.sql',
            '2000.sql',
            '3000.sql',
            '4000.sql',
        ]));

        assertEmpty($down);
    }

    /**
     * @test
     */
    public function migrate_target_up()
    {
        $migrations = [
            '1000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '4000.sql' => (new Migration())->setScript('x')->setApplied(false),
        ];

        list ($up, $down) = (new MigrationFilter())->migrate($migrations, '3000.sql');

        assertThat(array_keys($up), equalTo([
            '1000.sql',
            '2000.sql',
            '3000.sql',
        ]));

        assertEmpty($down);
    }

    /**
     * @test
     */
    public function migrate_target_down()
    {
        $migrations = [
            '1000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '4000.sql' => (new Migration())->setScript('x')->setApplied(false),
        ];

        list ($up, $down) = (new MigrationFilter())->migrate($migrations, '1000.sql');

        assertEmpty($up);
        assertThat(array_keys($down), equalTo([
            '2000.sql',
            '3000.sql',
        ]));
    }

    /**
     * @test
     */
    public function migrate_part()
    {
        $migrations = [
            '1000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(false),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '4000.sql' => (new Migration())->setScript('x')->setApplied(false),
        ];

        list ($up, $down) = (new MigrationFilter())->migrate($migrations, '3000.sql');

        assertThat(array_keys($up), equalTo([
            '2000.sql',
        ]));

        assertEmpty($down);
    }

    /**
     * @test
     * @dataProvider down_data
     */
    public function down_()
    {
        list ($missing, $all, $expected) = func_get_args();

        $migrations = [
            '1000.sql' => (new Migration())->setScript(null)->setApplied(true),
            '2000.sql' => (new Migration())->setScript('x')->setApplied(true),
            '3000.sql' => (new Migration())->setScript('x')->setApplied(false),
        ];

        $down = (new MigrationFilter())->down($migrations, $missing, $all);

        assertThat(array_keys($down), equalTo($expected));
    }

    public function down_data()
    {
        return [[
            0, 0, ['2000.sql'],

            // missing
            1, 0, ['1000.sql'],

            // all
            0, 1, ['1000.sql', '2000.sql'],
        ]];
    }
}
