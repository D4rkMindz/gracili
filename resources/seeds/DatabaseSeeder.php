<?php

use Phinx\Seed\AbstractSeed;
use Phinx\Seed\SeedInterface;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends AbstractSeed
{
    /**
     * Run
     */
    public function run()
    {
        $this->truncateTables();

        $this->runSeed(ACLSeed::class);
        $this->runSeed(LanguageSeed::class);
        $this->runSeed(UserSeed::class);

        // sql files can be loaded as followscx
        // $sql = file_get_contents(__DIR__ . '/example.sql');
        // $this->execute($sql);
    }

    /**
     * Truncate tables
     *
     * @return void
     */
    private function truncateTables()
    {
        $this->execute('SET FOREIGN_KEY_CHECKS = 0;');

        $this->truncateTableByName('user_has_role');
        $this->truncateTableByName('user_has_group');
        $this->truncateTableByName('group_has_role');
        $this->truncateTableByName('role');
        $this->truncateTableByName('group');
        $this->truncateTableByName('password_reset_request');
        $this->truncateTableByName('user');
        $this->truncateTableByName('language');

        $this->execute('SET FOREIGN_KEY_CHECKS = 1;');
    }

    /**
     * Truncate the given table
     *
     * @param string $tableName The name of the table to truncate
     *
     * @return void
     */
    private function truncateTableByName($tableName)
    {
        $table = $this->table($tableName);
        $table->truncate();
    }

    /**
     * @param string $class
     */
    private function runSeed(string $class)
    {
        /** @var SeedInterface $seed */
        $seed = new $class();
        $seed->setAdapter($this->getAdapter());
        $seed->setInput($this->getInput());
        $seed->setOutput($this->getOutput());
        $seed->run();
    }
}
