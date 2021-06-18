<?php


namespace WolfDen133\FormCreator\Bases;


class SimpleFormBase extends FormBase
{
    /** @var array */
    private array $elements;


    /**
     * SimpleFormBase constructor.
     * @param string $name
     * @param string $title
     * @param string $content
     * @param array $elements
     */
    public function __construct(string $name, string $title, string $content, array $elements)
    {
        $this->elements = $elements;

        parent::__construct($name, parent::SIMPLE, $title, $content);
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


}