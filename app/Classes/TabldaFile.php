<?php

namespace Vanguard\Classes;


class TabldaFile
{
    /**
     * @var string
     */
    protected $file_name;
    /**
     * @var string
     */
    protected $content;

    /**
     * TabldaFile constructor.
     * @param string $file_name
     * @param string $content
     */
    public function __construct(string $file_name = '', string $content = '')
    {
        $this->file_name = $file_name;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getClientOriginalName()
    {
        return $this->file_name;
    }

    /**
     * @param string $path
     */
    public function storeAs(string $path, string $name)
    {
        \Storage::put($path.'/'.$name, $this->content);
    }
}