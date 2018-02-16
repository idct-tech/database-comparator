<?php
namespace IDCT\Db\Tools\Compare\Source;

use IDCT\Db\Tools\Compare\Difference;
use IDCT\Db\Tools\Compare\Source\SourceInterface;
use PDO;

/**
 * PdoSource, class for database connections supported by PDO.
 */
class PdoSource implements SourceInterface
{
    /**
     * PDO instance.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Query for getting original data batch for comparison (original data),
     * should include :limit and :offset parameters to support.
     *
     * @var string
     */
    protected $queryAll;

    /**
     * Query for getting single entry from the current data source which will be
     * compared against data from the original set (retrieved using queryAll).
     *
     * Be sure to use {_keys} token in place where entry identifier is meant to
     * be placed.
     *
     * @var string
     */
    protected $querySingle;

    /**
     * Ignored fields array.
     *
     * ['field1', 'field2', 'field2']
     *
     * for example:
     *
     * ['last_updated', 'irrelevant_field']
     *
     * @var string[]
     */
    protected $ignoredFields;

    /**
     * Set of keys.
     *
     * ['field1', 'field2']
     *
     * for example:
     *
     * ['id', 'sub_id']
     *
     * @var string[]
     */
    protected $singleKeys;

    /**
     * If weak comparison is enabled every value is casted to string before
     * comparison.
     *
     * @var boolean
     */
    protected $weakComparison = false;

    /**
     * Pre-check transformation method. Used to modify values before comparison:
     * useful in case you know how differences should look in the new data source
     * and want o verify the modification (check README.md example).
     *
     * @var \Closure($their, $mine)
     */
    protected $transformation;

    /**
     * Enables weak comparison.
     *
     * When turned on all values are casted to strings before comparison.
     *
     * @return $this
     */
    public function enableWeakComparison()
    {
        $this->weakComparison = true;
        return $this;
    }

    /**
     * Disables weak comparison.
     *
     * When off values are compared in formats as returned from the data source.
     *
     * @return $this
     */
    public function disableWeakComparison()
    {
        $this->weakComparison = false;
        return $this;
    }

    /**
     * Sets the PDO connector.
     *
     * @param PDO $pdo
     * @return $this
     */
    public function setPdo(PDO $pdo)
    {
        $this->pdo = $pdo;

        return $this;
    }

    /**
     * Gets the PDO connector.
     *
     * @return $this
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Sets ignored fields.
     *
     * Useful when you want to avoid testing of a particular field and it would
     * cause a lot of complexity to remove it from the query. Thanks to this
     * method you can still use for instance SELECT * and exclude some fields,
     * like `last_updated`.
     *
     * @param string[] $ignoredFields
     * @return $this
     */
    public function setIgnoredFields($ignoredFields)
    {
        $this->ignoredFields = $ignoredFields;

        return $this;
    }

    /**
     * Sets keys which identify a single entry in the set. An array as key can
     * be defined by few fields.
     *
     * @param string[] $keys
     * @return $this
     */
    public function setSingleKeys($keys)
    {
        $this->singleKeys = $keys;

        return $this;
    }

    /**
     * Gets keys which identify a single entry in the set. An array as key can
     * be defined by few fields.
     *
     * @return string[]
     */
    public function getSingleKeys()
    {
        return $this->singleKeys;
    }

    /**
     * Gets ignored fields.
     *
     * Useful when you want to avoid testing of a particular field and it would
     * cause a lot of complexity to remove it from the query. Thanks to this
     * method you can still use for instance SELECT * and exclude some fields,
     * like `last_updated`.
     *
     * @return string[]
     */
    public function getIgnoredFields()
    {
        return $this->ignoredFields;
    }

    /**
     * Sets the query to get a batch (all) original data which will be used as a
     * base for comparisons. Should include :limit and :offset tokens.
     *
     * @param string $query
     * @return $this
     */
    public function setQueryAll($query)
    {
        $this->queryAll = $query;

        return $this;
    }

    /**
     * Sets the query to get a single entry which will be compared against original
     * data. Should include {_keys} token.
     *
     * @param string $query
     * @return $this
     */
    public function setQuerySingle($query)
    {
        $this->querySingle = $query;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAll($offset, $length)
    {
        $query = $this->queryAll;

        $sth = $this->getPdo()->prepare($query);
        $sth->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $sth->bindValue(':limit', $length, \PDO::PARAM_INT);
        $sth->execute();

        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getSingle($baseObject)
    {
        $query = $this->querySingle;
        $sth = $this->getPdo()->prepare($query);
        $keys = $this->getSingleKeys();

        foreach ($keys as $key) {
            $value = $baseObject[$key];

            $sth->bindValue(':' . $key, $value);//, PDO::PARAM_INT);
        }
        $sth->execute();

        $result = $sth->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    /**
     * Extracts single identifier's value from the original record.
     *
     * @param array $their
     * @return array
     */
    protected function getObjectId($their)
    {
        $identifiers = array();
        $keys = $this->getSingleKeys();
        foreach ($keys as $key) {
            if (isset($their[$key])) {
                $identifiers[$key] = $their[$key];
            }
        }

        if (empty($identifiers)) {
            return null;
        }

        return $identifiers;
    }

    /**
     * {@inheritDoc}
     */
    public function getSinglePreCheckTransformation()
    {
        return $this->transformation;
    }

    /**
     * Transformation of data retrieved from both data sources just before
     * comparison: useful for types casting or simple changes which are not so
     * major or relevant to build a new or child Source class.
     *
     * Sets the method used for transformaton: should accept original data as
     * first argument ($their) and data from current data source as second ($mine).
     * Must return affected data of the current data source ($mine).
     *
     * @return null|\Closure
     */
    public function setSinglePreCheckTransformation(\Closure $function)
    {
        $this->transformation = $function;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function compare($their, $mine)
    {
        $differences = array();
        $ignoredFields = $this->getIgnoredFields();

        if (($transformation = $this->getSinglePreCheckTransformation()) !== null) {
            list ($their, $mine) = $transformation($their, $mine);
        }

        $count = 0;

        foreach ($their as $field => $value) {
            if (in_array($field, $ignoredFields)) {
                continue;
            }

            if ($this->weakComparison) {
                $myValue = strval($mine[$field]);
                $value = strval($value);
            } else {
                $myValue = $mine[$field];
                $value = $value;
            }

            if ($value !== $myValue) {
                $difference = new Difference();
                $difference->setField($field)
                           ->setOriginalContent($value)
                           ->setNewContent($myValue);

                $differences[] = $difference;
                $count++;
            }
        }

        $differences['__id'] = $this->getObjectId($their);
        $differences['__count'] = $count;

        return $differences;
    }
}
