<?php
namespace IDCT\Db\Tools\Compare\Source;

interface SourceInterface {
    public function getAll($offset, $length);
    public function getSingle($baseObject);
    public function getSinglePreCheckTransformation();
    public function compare($their, $mine);
}
