<?php
namespace IDCT\Db\Tools\Compare\Output;

interface OutputInterface {

    public function reportDifferences($source, $id, $differences);

}
