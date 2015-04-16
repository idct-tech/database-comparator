<?php
namespace Casadatos\Compare;

use IDCT\Db\Tools\Compare\Output\OutputInterface as OutputInterface;

class SimpleOutput implements OutputInterface {
    protected $filename;
    protected $cleared = array();

    public function getBaseFilename() {
        return $this->filename;
    }

    public function setBaseFilename($filename) {
        $this->filename = $filename;

        return $this;
    }

    public function reportDifferences($source, $id, $differences) {
        $baseFileName = $this->getBaseFilename();
        $filename = str_replace('{source}', $source, $baseFileName);

        if(!in_array($filename, $this->cleared)) {
            $this->cleared[] = $filename;
            file_put_contents($filename, '');
        }

        $content = ">>>> OBJECT: " . print_r($id, true) . PHP_EOL . PHP_EOL;
        if(is_array($differences)) {
            if(!empty($differences)) {
                foreach($differences as $difference) {
                    $content .= "\tF: " . $difference->getField() . PHP_EOL;
                    $content .= "\tO: " . $difference->getOriginalContent() . PHP_EOL;
                    $content .= "\tN: " . $difference->getNewContent() . PHP_EOL;
                    $content .= PHP_EOL;
                }
                file_put_contents($filename, $content, FILE_APPEND);
            }
        } else {
            $content .= "! MISSING" . PHP_EOL . PHP_EOL;
            file_put_contents($filename, $content, FILE_APPEND);
        }

        return $this;
    }


}
