<?php


namespace WolfDen133\FormCreator;


class FormBase
{
    /* Form Data */
    /**
     * @var string
     */
    private $title;
    private $name;
    private $content;
    /** @var array */
    private $elements;


    /**
     * FormBase constructor.
     * @param string $name
     * @param string $title
     * @param string $content
     * @param array $elements
     */
    public function __construct(string $name, string $title, string $content, array $elements)
    {
        $this->name = $name;
        $this->title = $title;
        $this->content = $content;
        $this->elements = $elements;

        // TODO Custom forms
    }


    /**
     * Text inside the form
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }


    /**
     * Buttons inside the form (called elements for future Custom forms)
     *
     * @return array[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }


    /**
     * Form identifying name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * Form Title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }


}