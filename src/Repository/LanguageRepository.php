<?php

namespace App\Repository;

use App\Exception\RecordNotFoundException;
use App\Table\LanguageTable;

class LanguageRepository extends AppRepository
{
    private LanguageTable $languageTable;

    /**
     * Conostructor.
     *
     * @param LanguageTable $languageTable
     */
    public function __construct(LanguageTable $languageTable)
    {
        $this->languageTable = $languageTable;
    }

    /**
     * Get the language id by tag
     *
     * @param string $tag
     *
     * @return int
     * @throws RecordNotFoundException
     */
    public function getLanguageIdByTag(string $tag): int
    {
        $query = $this->languageTable->newSelect();
        $query->select(['id'])
            ->where(['tag' => $tag]);
        $result = $query->execute()->fetch('assoc');
        if (!empty($result)) {
            return (int)$result['id'];
        }

        throw new RecordNotFoundException(__('Language not found'), 'tag = ' . $tag);
    }
}