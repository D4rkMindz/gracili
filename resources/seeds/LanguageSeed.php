<?php


use App\Type\Language;
use Phinx\Seed\AbstractSeed;

class LanguageSeed extends AbstractSeed
{
    public const LANGUAGE_ID = [
        Language::EN_GB => 1,
        Language::DE_CH => 2,
        Language::FR_CH => 3,
    ];

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $data = [
            [
                'id' => self::LANGUAGE_ID[Language::EN_GB],
                'name' => 'English',
                'english_name' => 'English',
                'tag' => Language::EN_GB,
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::LANGUAGE_ID[Language::DE_CH],
                'name' => 'Deutsch',
                'english_name' => 'German',
                'tag' => Language::DE_CH,
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
            [
                'id' => self::LANGUAGE_ID[Language::FR_CH],
                'name' => 'Français',
                'english_name' => 'French',
                'tag' => Language::FR_CH,
                'created_at' => '2022-08-01 00:00:00',
                'created_by' => 0,
                'modified_at' => '2022-08-01 00:00:00',
                'modified_by' => 0,
            ],
        ];

        $this->table('language')->insert($data)->save();
    }
}
