<?php
namespace IDCT\Db\Tools\Compare;

interface OutputInterface {

    public function reportDifferences($source, $id, $differences);
    
}
