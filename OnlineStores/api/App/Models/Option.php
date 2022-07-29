<?php

namespace Api\Models;

class Option extends ParentModel
{
    /**
     * The array of available options types
     *
     * @var array
     */
    private $types = [
        'select',
        'radio',
        'checkbox',
        'image',
        'text',
        'textarea',
        'file',
        'date',
        'time',
        'datetime',
    ];

    /**
     * a special array of types
     *
     * @var array
     */
    private $selectTypes = [
        'select',
        'radio',
        'checkbox',
        'image',
    ];

    /**
     * Set the array of types
     *
     * @param array $types
     *
     * @return \Api\Models\Option
     */
    public function setTypes($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Inject new type to the current types array
     *
     * @param string $type
     *
     * @return \Api\Models\Option
     */
    public function addType($type)
    {
        $this->types[] = $type;

        return $this;
    }

    /**
     * Check if the types array has a specific type
     * mostly used in validation
     *
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        return (in_array($type, $this->getTypes()));
    }

    /**
     * Return the types array
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Check if the given string is in the selectTypes array
     *
     * @param string $type
     *
     * @return bool
     */
    public function isSelectType($type)
    {
        return (in_array($type, $this->selectTypes));
    }

    /**
     * Retrieve all options from the database.
     *
     * @return array
     */
    public function getAll()
    {
        $this->container->load->model('catalog/option');
        $options = $this->container->registry->get('model_catalog_option')->getOptions();
        return $options;
    }

    /**
     * Return option object using option id
     *
     * @param int $id
     *
     * @return array
     */
    public function getById($id)
    {
        $this->container->load->model('catalog/option');

        $optionsModel = $this->container->registry->get('model_catalog_option');

        $options = $optionsModel->getOption($id);

        if ($options) {
            $options['option_description'] = $this->prepareProductDescriptionGet(
                $optionsModel->getOptionDescriptions($id)
            );
        }

        return $options;
    }

    /**
     * Return option values array using option id
     *
     * @param int $id
     *
     * @return array
     */
    public function getOptionValues($id)
    {
        $this->container->load->model('catalog/option');

        $optionsModel = $this->container->registry->get('model_catalog_option');

        $option_value_data = $optionsModel->getOptionValues($id);


        return $option_value_data;
    }
	
	 /**
     * Retrieve filtered options from the database
	 * parm array $data ['filter_name'=>'',start=>'','limit=>'']
     *
     * @return array
     */
    public function getOptions($data)
    {
        $this->container->load->model('catalog/option');
        $options = $this->container->registry->get('model_catalog_option')->getOptions($data);
        return $options;
    }

    /**
     * Insert new option to the options tables and then retrieve it.
     *
     * @param array $data
     *
     * @return array|bool
     */
    public function storeOption($data)
    {
        $option = null;
        $this->container->load->model('catalog/option');

        $optionsModel = $this->container->registry->get('model_catalog_option');

        $data['option_description'] = $this->prepareProductDescriptionPost($data['option_description']);

        foreach ($data['option_value'] as &$optionValue) {
            $optionValue['option_value_description'] = $this->prepareProductDescriptionPost(
                $optionValue['option_value_description']
            );
        }

        $optionId = $optionsModel->addOption($data);

        if ($optionId > 0) {
            $option = $this->getById($optionId);
        }

        return $option;
    }

    /**
     * Update already exists option entry using its ID and return the new updated values
     *
     * @param int $optionId
     * @param array $data
     *
     * @return array|bool
     */
    public function updateOption($optionId, $data)
    {
        $this->container->load->model('catalog/option');

        $optionsModel = $this->container->registry->get('model_catalog_option');

        $data['option_description'] = $this->prepareProductDescriptionPost($data['option_description']);

        foreach ($data['option_value'] as &$optionValue) {
            $optionValue['option_value_description'] = $this->prepareProductDescriptionPost(
                $optionValue['option_value_description']
            );
        }

        $optionsModel->editOption($optionId, $data);

        return $this->getById($optionId);;
    }

    /**
     * Destroy an option entry using its option id
     *
     * @param int $optionId
     *
     * @return bool
     */
    public function deleteOption($optionId)
    {
        $this->container->load->model('catalog/option');

        $optionsModel = $this->container->registry->get('model_catalog_option');

        $optionsModel->deleteOption($optionId);

        return true;
    }

    /**
     * @param $option_value_id
     * @return array
     */
    public function getOptionValueDescription($option_value_id)
    {
        $this->container->load->model('catalog/option');
        $option_value_description = $this->container->registry->get('model_catalog_option')->getOptionValueDescription($option_value_id);
        $option_value_description_prepared = $this->prepareProductDescriptionGet(
            $option_value_description
        );
        return $option_value_description_prepared;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function storeOptionValues($data)
    {
        $this->container->load->model('catalog/option');
        $optionsModel = $this->container->registry->get('model_catalog_option');
        foreach ($data['option_value'] as &$optionValue) {
            $optionValue['option_value_description'] = $this->prepareProductDescriptionPost(
                $optionValue['option_value_description']
            );
        }
        return $optionValueId = $optionsModel->addOptionValues($data);;
    }
}