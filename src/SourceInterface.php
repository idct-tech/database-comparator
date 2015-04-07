<?php
namespace IDCT\Db\Tools\Compare;

interface SourceInterface {
    public function getAll($offset, $length);
    public function getOne($keys);    
    
}

