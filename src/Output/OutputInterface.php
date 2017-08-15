<?php
namespace IDCT\Db\Tools\Compare\Output;

/**
 * Interface for output writing: file, database, logging platform etc.
 * Differences are going to be reported using a class which implements this
 * interface.
 */
interface OutputInterface
{

    /**
     * Method used for reporting a single difference.
     *
     * @param string $source
     * @param string|int $id
     * @param array $differences
     * @return $this
     */
    public function reportDifferences($sourceName, $elementId, array $differences = null);
}
