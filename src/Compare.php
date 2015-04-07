<?php
namespace IDCT\Db\Tools;

use IDCT\Db\Tools\Compare\SourceInterface as DataSource;
use IDCT\Db\Tools\Compare\OutputInterface as OutputInterface;

class Compare {

    /**
     * @var array Data sources used in each batch
     */
    protected $sources;
    
    protected $currentOffset = 0;
    protected $bufferLength = 250;

    protected $output;
    
    protected function getIgnoredFieldsArray() {
        return is_array($this->ignoredFields) ? $this->ignoredFields : array();
    }
                            
    protected function hasMain() {
        return isset($this->sources['main']);
    }
    
    public function addSource($name, DataSource $source) {
        if($name === 'main' && $this->hasMain()) {
            throw new \Exception('Main data source already set');
        }
        
        $this->sources[$name] = $source;
        
        return $this;
    }
    
    public function getSource($name) {
        return $this->sources[$name];
    }
    
    protected function getSources() {
        return $this->sources;
    }
    
    public function resetSources() {
        $this->sources = array();
        
        return $this;
    }
    
    public function setOutput(OutputInterface $output) {
        $this->output = $output;
        
        return $this;
    }
    
    public function getOutput() {
        return $this->output;
    }
    
    protected function next() {
        if($this->hasMain() === false) {
            throw new \Exception('Missing main data source');   
        }
        
        $results = array();
        
        //collect a batch from the main data source
        $mainBatch = $this->getSource('main')->getAll($this->currentOffset, $this->bufferLength);
        $output = $this->getOutput();
        
        if($output === null) {
            throw new \Exception('Output not defined');   
        }
        
        foreach($this->getSources() as $name => $source) {
            if($name === 'main') {
                continue;
            }
            
            $output->reportSource($name);
            
            foreach($mainBatch as $dataObject) {
                $differences = $source->getAndCompare($dataObject);
                $output->reportDifferences($differences);    
            }
        }
        
        if(count($mainBatch) < $length) {
            return false;
        }
        
        $this->currentOffset += $this->bufferLength;
        
        return true;
    }
    
}
