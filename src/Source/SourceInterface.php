<?php

namespace IDCT\Db\Tools\Compare\Source;

/**
 * Interface for sources. Implement any connectors using this interface.
 */
interface SourceInterface
{
    /**
     * Method used for getting batches of results from the original datasource
     * for comparison.
     *
     * Returns data in a form of associative array:
     *
     * [0] => [
     *     ['field1'] => data,
     *     ['field2'] => data
     *     ],
     * [1] => [
     *     ['field1'] => data,
     *     ['field2'] => data
     *     ], ...
     *
     * @param int offset of data in the datasource (like in SQL LIMIT)
     * @param int length of dataset (like in SQL LIMIT)
     * @return array associative array of results
     */
    public function getAll($offset, $length);

    /**
     * Method for getting a single entry for comparison from the compared data
     * source.
     *
     * Original object is passed for getting keys required to retrieve same
     * entry from the compared data source.
     *
     * Returns object in a form of associative array with keys as field names.
     *
     * @param array $baseObject original object entry for comparison
     * @return array
     */
    public function getSingle($baseObject);

    /**
     * Transformation of data retrieved from both data sources just before
     * comparison: useful for types casting or simple changes which are not so
     * major or relevant to build a new or child Source class.
     *
     * Setter is not in the interface as it is not required by `Compare` class
     * and method itself may be internally in the source class. If setter is
     * required then it should be defined in the class.
     *
     * @return null|\Closure
     */
    public function getSinglePreCheckTransformation();

    /**
     * Performs the actual comparison on both objects:
     * - $their from the original data source, to which this data source is being
     * compared (got using queryAll)
     * - $mine data from the current data source (got using querySingle)
     *
     * @param array $their
     * @param array $mine
     * @return array array of differences
     * @todo type class for differences
     */
    public function compare($their, $mine);
}
