<?php

namespace IDCT\Db\Tools\Compare\Output;

use IDCT\Db\Tools\Compare\Difference;

/**
 * Basic reporter, saves differences in plain text format, in separate files,
 * as follows:
 *
 * File LEFT (original):
 * ====[ Object > single_entry_id_descriptor ]====
 *
 * > F: field name
 * > V: original value
 *
 * File RIGHT (compared datasource, "new")
 * ====[ Object > single_entry_id_descriptor ]====
 *
 * > F: field name
 * > V: new value
 *
 * or
 * > Missing in new dataset!
 */
class SplitFilesOutput extends TextFileOutput implements OutputInterface
{
    /**
     * Reports single row's differences.
     *
     * @param string $source
     * @param string[] $id
     * @param null|Difference[] $differences
     * @todo warning about {source} token missing
     * @todo make smarter check for the need of clearing files
     * @return $this
     */
    public function reportDifferences($sourceName, $id, array $differences = null)
    {
        /* gets the base filename with {source} token which will be replaced with
        the name of the currently compared data source */
        $flatId = $this->getFlatId($id);
        $filenameLeft = $this->getStoragePath() . $sourceName . '_' . $flatId . '_left.txt';
        $filenameRight = $this->getStoragePath() . $sourceName . '_' . $flatId . '_right.txt';

        $left = fopen($filenameLeft, "w");
        $right = fopen($filenameRight, "w");

        /* if array of differences is provided ... */
        if (is_array($differences)) {
            if (!empty($differences)) {
                foreach ($differences as $difference) {

                    // do the reporting
                    fputs($left, $content .= "> F: `" . $difference->getField() . '`' . PHP_EOL
                    . "> V: `" . $difference->getOriginalContent() . '`' . PHP_EOL . PHP_EOL);

                    fputs($right, $content .= "> F: `" . $difference->getField() . '`' . PHP_EOL
                    . "> V: `" . $difference->getNewContent() . '`' . PHP_EOL . PHP_EOL);
                }
                file_put_contents($filename, $content, FILE_APPEND);
            }
        } else {
            fwrite($right, 'Missing in new dataset!');
        }

        fclose($left);
        fclose($right);

        return $this;
    }
}
