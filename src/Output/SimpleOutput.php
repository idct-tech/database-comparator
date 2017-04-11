<?php
namespace IDCT\Db\Tools\Compare\Output;

use IDCT\Db\Tools\Compare\Output\OutputInterface as OutputInterface;

class SimpleOutput implements OutputInterface
{
    protected $filename;
    protected $cleared = array();

    public function getBaseFilename()
    {
        return $this->filename;
    }

    public function setBaseFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    protected function getFlatId($id)
    {
        $id = '';
        foreach ($id as $key => $value) {
            $id .= ',' . $key . ': `' . $value . '`';
        }
        return substr($id, 1);
    }

    public function reportDifferences($source, $id, $differences)
    {
        $baseFileName = $this->getBaseFilename();
        $filename = str_replace('{source}', $source, $baseFileName);

        if (!in_array($filename, $this->cleared)) {
            $this->cleared[] = $filename;
            file_put_contents($filename, '');
        }

        $content = '====[ Object > ' . $this->getFlatId($id) . ' ]====' . PHP_EOL;

        if (is_array($differences)) {
            if (!empty($differences)) {
                foreach ($differences as $difference) {
                    $content .= "> F: `" . $difference->getField() . '`' . PHP_EOL;
                    $content .= "> O: `" . $difference->getOriginalContent() . '`' . PHP_EOL;
                    $content .= "> N: `" . $difference->getNewContent() . '`' . PHP_EOL;
                    $content .= PHP_EOL;
                }
                file_put_contents($filename, $content, FILE_APPEND);
            }
        } else {
            $content .= "> Missing in new dataset!" . PHP_EOL;
            file_put_contents($filename, $content, FILE_APPEND);
        }

        return $this;
    }
}
