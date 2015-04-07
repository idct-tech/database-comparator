<?php
namespace IDCT\Db\Tools\Compare;

class Difference {
    
    protected $original;
    protected $new;
    protected $field;
    
    public function setOriginalContent($content) {
        $this->original = $content;
        
        return $this;
    }
    
    public function setNewContent($content) {
        $this->new = $content;
        
        return $this;
    }
    
    public function getOriginalContent() {
        return $this->original;    
    }
    
    public function getNewContent() {
        return $this->new;
    }
    
    public function setField($field) {
        $this->field = $field;
        
        return $this;
    }
    
    public function getField() {
        return $this->field;
    }
    
    
    
}