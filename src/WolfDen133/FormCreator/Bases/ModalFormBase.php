<?php

namespace WolfDen133\FormCreator\Bases;

class ModalFormBase extends FormBase
{

    /** @var array */
    private array $button1;
    private array $button2;


    /**
     * ModalFormBase constructor.
     * @param string $name
     * @param string $title
     * @param string $content
     * @param array $button1
     * @param array $button2
     */
    public function __construct(string $name, string $title, string $content, array $button1, array $button2)
    {
        parent::__construct($name, self::MODAL, $title, $content);

        $this->button1 = $button1;
        $this->button2 = $button2;
    }

    /**
     * Returns the buttons
     *
     * @return array
     */
    public function getButton1 () : array
    {
        return $this->button1;
    }

    /**
     * @return array
     */
    public function getButton2 () : array
    {
        return $this->button2;
    }
}