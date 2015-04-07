<?php
namespace IDCT\Db\Tools\Compare;

interface SourceInterface {    
    public function getAll($offset, $length);        
    public function getSingle($baseObject);
    public function compare($their, $mine);
}

