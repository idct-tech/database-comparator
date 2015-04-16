<?php
namespace IDCT\Db\Tools\Compare\Source;

use IDCT\Db\Tools\Compare\Difference as Difference;
use IDCT\Db\Tools\Compare\Source\SourceInterface as SourceInterface;

class PdoSource implements SourceInterface {

    protected $pdo;

    protected $queryAll;
    protected $querySingle;
    protected $ignoredFields;
    protected $singleKeys;

    protected $transformation;

    public function setPdo($pdo) {
        $this->pdo = $pdo;

        return $this;
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function setIgnoredFields($ignoredFields) {
        $this->ignoredFields = $ignoredFields;

        return $this;
    }

    public function setSingleKeys($keys) {
        $this->singleKeys = $keys;

        return $this;
    }

    public function getSingleKeys() {
        return $this->singleKeys;
    }

    public function getIgnoredFields() {
        return $this->ignoredFields;
    }

    public function setQueryAll($query) {
        $this->queryAll = $query;

        return $this;
    }

    public function setQuerySingle($query) {
        $this->querySingle = $query;

        return $this;
    }

    public function getAll($offset, $length) {
        $query = $this->queryAll;

        $sth = $this->getPdo()->prepare($query);
        $sth->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $sth->bindValue(':limit', $length, \PDO::PARAM_INT);
        $sth->execute();

        $result = $sth->fetchAll(\PDO::FETCH_ASSOC);

        return $result;
    }

    public function getSingle($baseObject) {
        $query = $this->querySingle;
        $sth = $this->getPdo()->prepare($query);
        $keys = $this->getSingleKeys();

        foreach($keys as $key) {
            $value = $baseObject[$key];

            $sth->bindValue(':' . $key, $value);//, PDO::PARAM_INT);
        }
        $sth->execute();

        $result = $sth->fetch(\PDO::FETCH_ASSOC);

        return $result;
    }

    protected function getObjectId($their) {

        $identifiers = array();
        $keys = $this->getSingleKeys();
        foreach($keys as $key) {
            if(isset($their[$keys])) {
                $identifiers[$keys] = $their[$keys];
            }
        }

        if(empty($identifiers)) {
            return null;
        }

        return $identifiers;
    }

    public function getSinglePreCheckTransformation() {
        return $this->transformation;
    }

    public function setSinglePreCheckTransformation(\Closure $function) {
        $this->transformation = $function;

        return $this;
    }

    public function compare($their, $mine) {

        $differences = array();
        $ignoredFields = $this->getIgnoredFields();

        if(($transformation = $this->getSinglePreCheckTransformation()) !== null) {
            $mine = $transformation($their, $mine);
        }

        foreach($their as $field => $value) {
            if(in_array($field, $ignoredFields)) {
                continue;
            }

            $myValue = $mine[$field];

            if($value !== $myValue) {
                $difference = new Difference();
                $difference->setField($field)
                           ->setOriginalContent($value)
                           ->setNewContent($myValue);

                $differences[] = $difference;
            }
        }

        $differences['__id'] = $this->getObjectId($their);

        return $differences;
    }

}