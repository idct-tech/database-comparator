<?php
namespace IDCT\Db\Tools\Compare;

interface OutputInterface {
    
    public function reportSource($name);
    public function reportDifferences($differences);
    
}
