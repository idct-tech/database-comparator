<?php
namespace IDCT\Db\Tools\Compare;

/**
 * Class for representation of a single object's difference.
 */
class Difference
{

    /**
     * Original content: from original data source.
     *
     * @var string|int|null|mixed
     */
    protected $original;

    /**
     * New content: from "current" data source compared against "main" one.
     *
     * @var string|int|null|mixed
     */
    protected $new;

    /**
     * Field name.
     *
     * @var string
     */
    protected $field;

    /**
     * Sets the original field's content: from original data source.
     *
     * @var string|int|null|mixed
     * @return $this
     */
    public function setOriginalContent($content)
    {
        $this->original = $content;

        return $this;
    }

    /**
     * Sets the field's new content: from "current" data source compared against
     * "main" one.
     *
     * @var string|int|null|mixed
     * @return $this
     */
    public function setNewContent($content)
    {
        $this->new = $content;

        return $this;
    }

    /**
     * Gets the original field's content: from original data source.
     *
     * @return string|int|null|mixed
     */
    public function getOriginalContent()
    {
        return $this->original;
    }

    /**
     * Gets the field's new content: from "current" data source compared against
     * "main" one.
     *
     * @var string|int|null|mixed
     * @return $this
     */
    public function getNewContent()
    {
        return $this->new;
    }

    /**
     * Set field's name.
     *
     * @param string $field
     * @return $this
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field's name.
     *
     * @return $this
     */
    public function getField()
    {
        return $this->field;
    }
}
