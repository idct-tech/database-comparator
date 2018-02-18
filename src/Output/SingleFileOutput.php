<?php

namespace IDCT\Db\Tools\Compare\Output;

use IDCT\Db\Tools\Compare\Difference;

/**
 * Basic reporter, saves differences in plain text format, as follows:
 *
 * ====[ Object > single_entry_id_descriptor ]====
 *
 * > F: field name
 * > O: original value
 * > N: new value
 *
 * > F: field name
 * > O: original value
 * > N: new value
 *
 * or
 * > Missing in new dataset!
 */
class SingleFileOutput extends TextFileOutput implements OutputInterface
{
    /**
     * Array of already cleared files (file paths)
     *
     * @var string[]
     */
    protected $cleared = [];

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
        $filename = $this->getStoragePath() . 'comparison_with_' . $sourceName . '.txt';

        /* checks if currently processed file needs clearing */
        if (!in_array($filename, $this->cleared)) {
            $this->cleared[] = $filename;
            file_put_contents($filename, '');
        }

        /* sets the title - first content line */
        $content = '====[ Object > ' . $this->getFlatId($id) . ' ]====' . PHP_EOL;

        /* if array of differences is provided ... */
        if (is_array($differences)) {
            if (!empty($differences)) {
                foreach ($differences as $difference) {

                    // do the reporting
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
