<?php


namespace WolfDen133\FormCreator\Bases;


class FormBase
{
    public const SIMPLE = 0;
    public const MODAL = 1;
    public const CUSTOM = 3; //TODO

    /** @var string */
    private $title;
    private $content;
    private $name;

    /** @var int */
    private $type;


    /**
     * FormBase constructor.
     * @param string $name
     * @param string $title
     * @param string $content
     */
    public function __construct(string $name, int $type, string $title, string $content)
    {
        $this->title = $title;
        $this->type = $type;
        $this->content = $content;
        $this->name = $name;
    }


    /**
     * @param string $title
     */
    public function setTitle (string $title) : void
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle () : string
    {
        return $this->title;
    }


    /**
     * @param string $content
     */
    public function setContent (string $content) : void
    {
        $this->content = $content;
    }


    /**
     * @return string
     */
    public function getContent () : string
    {
        return $this->content;
    }


    /**
     * @return string
     */
    public function getName () : string
    {
        return $this->name;
    }


    /**
     * @return int
     */
    public function getType () : int
    {
        return $this->type;
    }

}