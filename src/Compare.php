<?php
namespace IDCT\Db\Tools;

use IDCT\Db\Tools\Compare\Source\SourceInterface;
use IDCT\Db\Tools\Compare\Output\OutputInterface;
use IDCT\Db\Tools\Compare\Filters\FilterInterface;

/**
 * Main comparator class
 */
class Compare
{
    /**
     * Array of sources assigned to the comparison job.
     *
     * @var SourceInterface[]
     */
    protected $sources;

    /**
     * Pointer to the actual offset of the original data source.
     *
     * @var int
     */
    protected $currentOffset = 0;

    /**
     * Offset length for data fetching fromt he original data source.
     *
     * @var int
     */
    protected $bufferLength = 250;

    /**
     * Output to which differences are written.
     *
     * @var OutputInterface
     */
    protected $output;

    /**
     * Checks if the main data source is set.
     *
     * Main data source is the one to which we compare all the others.
     * It must be named "main".
     *
     * @return boolean
     */
    protected function hasMain()
    {
        return isset($this->sources['main']);
    }

    /**
     * Adds data source.
     *
     * Important: one data source must be named "main": it is the data source
     * to which we compare all the others.
     *
     * @param string $name
     * @param SourceInterface $source
     * @return $this
     */
    public function addSource($name, SourceInterface $source)
    {
        if ($name === 'main' && $this->hasMain()) {
            throw new \Exception('Main data source already set');
        }

        $this->sources[$name] = $source;

        return $this;
    }

    /**
     * Gets the data source by name.
     *
     * @param string $name
     * @return SourceInterface|null
     */
    public function getSource($name)
    {
        return $this->sources[$name];
    }

    /**
     * Gets all data sources assigned to this comparison tool.
     *
     * @return SourceInterface[string]|null
     */
    protected function getSources()
    {
        return $this->sources;
    }

    /**
     * Clears the sources array.
     *
     * @return $this
     */
    public function resetSources()
    {
        $this->sources = array();

        return $this;
    }

    /**
     * Sets the output object.
     *
     * @param OutputInterface $output
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Gets the output object.
     *
     * @return OutputInterface|null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Sets the buffer length for data batches (understood as length similar to
     * length in SQL LIMIT).
     *
     * @param int $len
     * @return $this
     */
    public function setBufferLength($len)
    {
        $this->bufferLength = $len;

        return $this;
    }

    /**
     * Performs a batch of comparisons.
     *
     * Returns false when done.
     *
     * @return boolean
     */
    protected function next()
    {
        if ($this->hasMain() === false) {
            throw new \Exception('Missing main data source');
        }

        $results = array();

        /* collects a batch from the main data source - this data source is used
        for all comparisons */
        $mainBatch = $this->getSource('main')->getAll($this->currentOffset, $this->bufferLength);

        // finished
        if (empty($mainBatch)) {
            return false;
        }

        // gets the output object
        $output = $this->getOutput();
        if ($output === null) {
            throw new \Exception('Output not defined');
        }

        // for every source ...
        foreach ($this->getSources() as $name => $source) {
            if ($name === 'main') {
                continue;
            }

            // for every entry in the main data batch ...
            foreach ($mainBatch as $dataObject) {
                /* get object from the current datasource which should be the
                representation of the original data object based on the provided
                set of keys */
                $currentObject = $source->getSingle($dataObject);

                foreach ($this->filterChain as $filter) {
                    list ($dataObject, $currentObject) = $filter->filter($dataObject, $currentObject);
                }

                // perform the comparison
                $differences = $source->compare($dataObject, $currentObject);

                // internal id of the object
                $id = $differences['__id'];

                // log differences count
                $this->diffsCount[$name] += $differences['__count'];

                /* we do not need the internals anymore: this will be removed
                when i switch here to objects */
                unset($differences['__id']);
                unset($differences['__count']);

                // if object was not found report with null
                $output->reportDifferences($name, $id, ($currentObject === false ? null : $differences));
            }
        }

        $this->currentOffset += $this->bufferLength;

        return true;
    }

    public function addFilter(FilterInterface $filter) {
        $this->filterChain[] = $filter;
        return $this;
    }

    public function clearFliters() {
        $this->filterChain = [];
        return $this;
    }

    /**
     * Returns differences counts per data source.
     *
     * @return int[string]
     */
    public function getDifferencesCount()
    {
        return $this->diffsCount;
    }

    /**
     * Performs the comparison.
     *
     * @return $this
     */
    public function run()
    {
        $this->diffsCount = [];

        foreach ($this->getSources() as $name => $source) {
            $this->diffsCount[$name] = 0;
        }

        while ($this->next()) {
        };

        return $this;
    }

    public function __construct()
    {
        $this->clearFilters();
    }
}
