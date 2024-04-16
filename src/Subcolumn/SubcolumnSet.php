<?php

namespace HeimrichHannot\SubColumnsBootstrapBundle\Subcolumn;

class SubcolumnSet
{
    /** @readonly  */
    public array $sets;
    /** @readonly  */
    public string $setName;
    /** @readonly  */
    public string $name;
    /** @readonly  */
    public string $source;

    private int $colCount;

    /**
     * Do not use this constructor directly. Use ElementHelper::getSet() instead.
     */
    public function __construct(
        string $source,
        string $name,
        string $setName,
        array  $sets
    )
    {
        $this->source = $source;
        $this->name = $name;
        $this->setName = $setName;
        $this->sets = $sets;
    }

    public function getColCount(): int
    {
        if (empty($this->colCount)) {
            $this->colCount = count($this->sets);
        }
        return $this->colCount;
    }
}