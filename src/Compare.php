<?php
namespace IDCT\Db\Tools;

use IDCT\Db\Tools\Compare\Source\SourceInterface as DataSource;
use IDCT\Db\Tools\Compare\Output\OutputInterface as OutputInterface;

class Compare
{

    /**
     * @var array Data sources used in each batch
     */
    protected $sources;

    protected $currentOffset = 0;
    protected $bufferLength = 250;

    protected $output;

    protected function getIgnoredFieldsArray()
    {
        return is_array($this->ignoredFields) ? $this->ignoredFields : array();
    }

    protected function hasMain()
    {
        return isset($this->sources['main']);
    }

    public function addSource($name, DataSource $source)
    {
        if ($name === 'main' && $this->hasMain()) {
            throw new \Exception('Main data source already set');
        }

        $this->sources[$name] = $source;

        return $this;
    }

    public function getSource($name)
    {
        return $this->sources[$name];
    }

    protected function getSources()
    {
        return $this->sources;
    }

    public function resetSources()
    {
        $this->sources = array();

        return $this;
    }

    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function setBufferLength($len)
    {
        $this->bufferLength = $len;

        return $this;
    }

    protected function next()
    {
        if ($this->hasMain() === false) {
            throw new \Exception('Missing main data source');
        }

        $results = array();

        //collect a batch from the main data source
        $mainBatch = $this->getSource('main')->getAll($this->currentOffset, $this->bufferLength);

        $output = $this->getOutput();

        if ($output === null) {
            throw new \Exception('Output not defined');
        }

        foreach ($this->getSources() as $name => $source) {
            if ($name === 'main') {
                continue;
            }

            $this->getLastDifferencesCount[$name] = 0;

            foreach ($mainBatch as $dataObject) {
                $rightObject = $source->getSingle($dataObject);
                $differences = $source->compare($dataObject, $rightObject);
                $id = $differences['__id'];
                $this->getLastDifferencesCount[$name] += $differences['__count'];
                unset($differences['__id']);
                unset($differences['__count']);
                if ($rightObject === false) {
                    $output->reportDifferences($name, $id, null);
                } else {
                    $output->reportDifferences($name, $id, $differences);
                }
            }
        }

        if (count($mainBatch) < $this->bufferLength) {
            return false;
        }

        $this->currentOffset += $this->bufferLength;

        return true;
    }

    public function getLastDifferencesCount()
    {
        return $this->lastDifferencesCount;
    }

    public function run()
    {
        $this->lastDifferencesCount = [];
        while ($this->next()) {
        };
    }
}
