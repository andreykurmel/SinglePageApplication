<?php

namespace Vanguard\Classes;


class FormulaSymbolCreator
{
    protected $saved_symbols = [];

    /**
     * Get 'FormulaSymbol' from Column Name.
     *
     * @param string $name
     * @return string
     */
    public function fromName(string $name)
    {
        $name = explode(',', preg_replace('/\s/i', '', $name));
        $result = array_shift($name);
        foreach ($name as $word) {
            $result .= '_' . $word[0];
        }

        return $this->addIfIndex($result);
    }

    /**
     * Add '_{idx}' to the string if it duplicates in saved_symbols.
     *
     * @param string $str
     * @return string
     */
    protected function addIfIndex(string $str)
    {
        if (!in_array($str, $this->saved_symbols)) {
            $this->saved_symbols[] = $str;
            return $str;
        }

        $idx = 1;
        while (in_array($str . '_' . $idx, $this->saved_symbols)) {
            $idx++;
        }

        $this->saved_symbols[] = $str . '_' . $idx;
        return $str . '_' . $idx;
    }

    /**
     * Clear Symbols
     */
    public function clearSavedSymbols()
    {
        $this->saved_symbols = [];
    }
}