<?php

namespace IDCT\Db\Tools\Compare\Output;

use RuntimeException;

/**
 * Basic reporter, saves differences in plain text format. Subtypes defines format.
 */
abstract class TextFileOutput implements OutputInterface
{
    protected $storagePath;

    public function setStoragePath($storagePath)
    {
        if (!is_dir($storagePath) || !$is_writable($storagePath)) {
            throw new RuntimeException('$storagePath must be a writable directory. Provider: `'.$storagePath.'`.');
        }

        if (substr($storagePath, -1, 1) !== DIRECTORY_SEPARATOR) {
            $storagePath .= DIRECTORY_SEPARATOR;
        }

        $this->storagePath = $storagePath;

        return $this;
    }

    public function getStoragePath()
    {
        return $this->storagePath;
    }

    abstract public function reportDifferences($sourceName, $id, array $differences = null);

    /**
     * Returns entry row id in a form of plain string: if id is complex writes
     * them after commas.
     *
     * @param string[] $idValues
     * @return string
     */
    protected function getFlatId($idValues)
    {
        $id = '';
        foreach ($idValues as $key => $value) {
            $id .= ',' . $key . ': `' . $value . '`';
        }

        return substr($id, 1);
    }
}
