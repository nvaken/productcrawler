<?php

namespace App\Console;

class ChoiceQuestion extends \Symfony\Component\Console\Question\ChoiceQuestion
{
    /**
     * @var bool|null
     */
    private $useKeyAsValue;

    public function __construct($question, array $choices, $useKeyAsValue = null, $default = null)
    {
        $this->useKeyAsValue = $useKeyAsValue;
        parent::__construct($question, $choices, $default);
    }

    protected function isAssoc($array)
    {
        return $this->useKeyAsValue !== null ? (bool)$this->useKeyAsValue : parent::isAssoc($array);
    }
}
