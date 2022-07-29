<?php

namespace ExpandCart\Foundation\String\Barcode;

use \Picqer\Barcode\BarcodeGenerator;
use \Picqer\Barcode\BarcodeGeneratorPNG;

class Generator
{
    /**
     * The barcode string.
     *
     * @var string
     */
    protected $barcode = null;

    /**
     * The barcode type.
     *
     * @var string
     */
    protected $type = 'TYPE_EAN_13';

    /**
     * The available barcode types.
     *
     * @var array
     */
    protected $types = [
        'TYPE_CODE_39',
        'TYPE_CODE_39_CHECKSUM',
        'TYPE_CODE_39E',
        'TYPE_CODE_39E_CHECKSUM',
        'TYPE_CODE_93',
        'TYPE_STANDARD_2_5',
        'TYPE_STANDARD_2_5_CHECKSUM',
        'TYPE_INTERLEAVED_2_5',
        'TYPE_INTERLEAVED_2_5_CHECKSUM',
        'TYPE_CODE_128',
        'TYPE_CODE_128_A',
        'TYPE_CODE_128_B',
        'TYPE_CODE_128_C',
        'TYPE_EAN_2',
        'TYPE_EAN_5',
        'TYPE_EAN_8',
        'TYPE_EAN_13',
        'TYPE_UPC_A',
        'TYPE_UPC_E',
        'TYPE_MSI',
        'TYPE_MSI_CHECKSUM',
        'TYPE_POSTNET',
        'TYPE_PLANET',
        'TYPE_RMS4CC',
        'TYPE_KIX',
        'TYPE_IMB',
        'TYPE_CODABAR',
        'TYPE_CODE_11',
        'TYPE_PHARMA_CODE',
        'TYPE_PHARMA_CODE_TWO_TRACKS',
    ];

    /**
     * Set the barcode type.
     *
     * @param string $type
     *
     * @return Generator
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set the barcode string.
     *
     * @param string $barcode
     *
     * @return Generator
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Generate the image.
     *
     * @return string
     */
    public function generate()
    {
        $generator = new BarcodeGeneratorPNG();

        if ($this->barcode == null) {
            throw new \Exception('Invalid barcode');
        }

        $type = $this->analyzeType();

        try {
            return base64_encode($generator->getBarcode($this->barcode, $type));
        } catch(\Exception $e) {
            return false;
        }
    }

    /**
     * Analyze and check if the given type is valid.
     *
     * @return string
     */
    protected function analyzeType()
    {
        $types = array_flip($this->types);

        if (isset($types[$this->type]) === false) {

            $this->type = 'TYPE_CODE_128';
        }

        $type = '\Picqer\Barcode\BarcodeGeneratorPNG::' . $this->type;

        return constant($type);
    }
}
