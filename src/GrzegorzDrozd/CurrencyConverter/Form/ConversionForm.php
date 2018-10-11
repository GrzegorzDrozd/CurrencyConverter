<?php
namespace GrzegorzDrozd\CurrencyConverter;

use Zend\Form\Form;

/**
 * Currency conversion form.
 *
 * @package GrzegorzDrozd\CurrencyConverter
 */
class ConversionForm extends Form {

    /**
     * ConversionForm constructor.
     * @param null $name
     * @param array $options
     */
    public function __construct($name = null, array $options = []) {
        parent::__construct('currency_converter', $options);

        $this->add([
            'name'  => 'source_currency',
            'type'  => 'hidden',
            'attributes' => [
                'value' => 'RUB',
            ],
        ]);
        $this->add([
            'name'  => 'target_currency',
            'type'  => 'hidden',
            'attributes' => [
                'value' => 'PLN',
            ],
        ]);
        $this->add([
            'name'  => 'amount',
            'type'  => 'text',
            'options' => [
                'label'=>'RUB'
            ],
        ]);
        $this->add([
            'name'  => 'converted',
            'type'  => 'text',
            'options' => [
                'label'=>'PLN'
            ],
        ]);
        $this->add([
            'name'  => 'convert',
            'type'  => 'submit',
            'attributes' => [
                'value' => 'Convert',
            ],
        ]);
    }
}
