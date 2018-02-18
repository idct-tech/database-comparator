<?php

namespace IDCT\Db\Tools\Compare\Filters;

interface FilterInterface
{
    public function filter($left, $right);
}
