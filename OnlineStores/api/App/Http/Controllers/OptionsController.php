<?php

namespace Api\Http\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class OptionsController extends Controller
{
	private $option;
	
	public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->option = $this->container['option'];
    }


    public function index(Request $request, Response $response)
    {
        $options = $this->container->option->getAll();
        if (!empty($options))
        {
            $data['product_option'] = array();
            foreach ($options as $option)
            {
                $option_info = $this->option->getById($option['option_id']);
                if (
                    $option_info['type'] == 'select' ||
                    $option_info['type'] == 'radio' ||
                    $option_info['type'] == 'checkbox' ||
                    $option_info['type'] == 'image' ||
                    $option_info['type'] == 'product'
                ) {

                    $option_values = $this->option->getOptionValues($option['option_id']);
                    $option_value_data = array();
                    foreach ($option_values as $option_value)
                    {
                        $option_value_data[] = array(
                            'option_value_id' => $option_value['option_value_id'],
                            'option_value_description' => $this->option->getOptionValueDescription($option_value['option_value_id']),
                        );
                    }
                    $data['product_option'][] = array(
                        'option_id' => $option_info['option_id'],
                        'name' => $option_info['name'],
                        'type' => $option_info['type'],
                        'option_description' => $option_info['option_description'],
                        'product_option_value' => $option_value_data,
                    );
                }
            }

        }
        //options types
        $data['optionTypes'] = [
            'text_choose' => [
                'select',
                'radio',
                'checkbox',
                'image',
            ],
            'text_input' => [
                'text',
                'textarea',
            ],
            'text_file' => [
                'file'
            ],
            'text_date' => [
                'date',
                'time',
                'datetime',
            ]
        ];
        return $response->withJson([
            'status' => 'ok',
            'data' => $data
        ]);
    }

    public function show(Request $request, Response $response, $args)
    {
        $validator = $this->validator([
            'id' => $args['id']
        ], [
            'id' => 'int'
        ]);

        if (!$validator) {
            return $response->withJson([
                'status' => $this->status[2]
            ]);
        }

        $option = $this->container->option->getById($args['id']);

        if (!$option) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[0],
                'error_description' => ''
            ]);
        }

        return $response->withJson([
            'status' => $this->status[1],
            'data' => $option
        ]);
    }

    public function store(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        $optionsDescription = $parameters['option_description'];

        $inputs = $rules = [];

        foreach ($optionsDescription as $code => $description) {
            $inputs["{$code}_name"] = $description['name'];
            $rules["{$code}_name"] = 'string|required';
        }
        $inputs["type"] = $parameters['type'];
        $rules["type"] = 'string|required';

        if ($this->container->option->hasType($parameters['type']) === false) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        $validator = $this->validator($inputs, $rules);

        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        if ($this->container->option->isSelectType($parameters['type'])) {

            $optionValues = $parameters['option_value'];

            $inputs = $rules = [];

            foreach ($optionValues as $optionValue) {
                foreach ($optionValue['option_value_description'] as $code => $description) {
                    $inputs["{$code}_name"] = $description['name'];
                    $rules["{$code}_name"] = 'string|required';
                }
            }

            $validator = $this->validator($inputs, $rules);

            if (!$validator) {
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => ''
                ], 406);
            }

        }

        $data['product_option'] = array();
        if ($createdOption = $this->container->option->storeOption($parameters)) {
            $option_values = $this->option->getOptionValues($createdOption['option_id']);
            $option_value_data = array();
            foreach ($option_values as $option_value)
            {
                $option_value_data[] = array(
                    'option_value_id' => $option_value['option_value_id'],
                    'option_value_description' => $this->option->getOptionValueDescription($option_value['option_value_id']),
                );
            }
            $data['product_option'][] = array(
                'option_id' => $createdOption['option_id'],
                'name' => $createdOption['name'],
                'type' => $createdOption['type'],
                'option_description' => $createdOption['option_description'],
                'product_option_value' => $option_value_data,
            );
            return $response->withJson([
                'status' => $this->status[1],
                'message' => 'Option added successfully',
                'data' => $data,
            ], 201);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => ''
        ], 400);
    }

    public function update(Request $request, Response $response, $args)
    {
        $parameters = $request->getParsedBody();

        $optionsDescription = $parameters['option_description'];

        $inputs = $rules = [];

        foreach ($optionsDescription as $code => $description) {
            $inputs["{$code}_name"] = $description['name'];
            $rules["{$code}_name"] = 'string';
        }
        $inputs["type"] = $parameters['type'];
        $rules["type"] = 'string';
        $inputs['id'] = $args['id'];
        $rules['id'] = 'required|int';

        if (isset($parameters['type']) && $this->container->option->hasType($parameters['type']) === false) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        $validator = $this->validator($inputs, $rules);

        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        if (isset($parameters['option_value']) && $this->container->option->isSelectType($parameters['type'])) {

            $optionValues = $parameters['option_value'];

            $inputs = $rules = [];

            foreach ($optionValues as $optionValue) {
                foreach ($optionValue['option_value_description'] as $code => $description) {
                    $inputs["{$code}_name"] = $description['name'];
                    $rules["{$code}_name"] = 'string';
                }
            }

            $validator = $this->validator($inputs, $rules);

            if (!$validator) {
                return $response->withJson([
                    'status' => 'error',
                    'status_code' => $this->status[2],
                    'error_description' => ''
                ], 406);
            }

        }

        $option = $this->container->option->getById($args['id']);

        if (!$option) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        if ($updatedOption = $this->container->option->updateOption($args['id'], $parameters)) {
            return $response->withJson([
                'status' => $this->status[1],
                'data' => $updatedOption,
            ], 201);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => ''
        ], 400);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $validator = $this->validator([
            'id' => $args['id']
        ], [
            'id' => 'required|int'
        ]);

        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        $this->container->option->deleteOption($args['id']);

        return $response->withJson(['status' => $this->status[1]], 202);
    }

	public function  autocomplete(Request $request, Response $response)
	{
		$params = $_GET;
		$data = array();

		if (isset($params['filter_name'])) {
            
			$filter_data = array(
				'filter_name' => $params['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);

			$options = $this->option->getOptions($filter_data);

			foreach ($options as $option) {
				$option_value_data = array();

				if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' || $option['type'] == 'image' || $option['type'] == 'product') {
					$option_values = $this->option->getOptionValues($option['option_id']);

					foreach ($option_values as $option_value) {
						if ($option_value['image']) {
							$image = $option_value['image'];
						} else {
							$image = '';
						}

						$option_value_data[] = array(
							'option_value_id' => $option_value['option_value_id'],
							'name'            => html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8'),
							'image'           => $this->container['image']->resize($image, 50, 50),
						);
					}

					$sort_order = array();

					foreach ($option_value_data as $key => $value) {
						$sort_order[$key] = $value['name'];
					}

					array_multisort($sort_order, SORT_ASC, $option_value_data);
				}

				$type = '';


				$data[] = array(
					'option_id'    => $option['option_id'],
					'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')),
					'category'     => $type,
					'type'         => $option['type'],
					'option_value' => $option_value_data
				);
			}
		}

		$sort_order = array();

		foreach ($data as $key => $value) {
			$sort_order[$key] = $value['name'];
		}

		array_multisort($sort_order, SORT_ASC, $data);
		return $response->withJson(['status' => 'ok', 'data' => $data]);
	}

	public function getOptionValues(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();
        if(!isset($parameters['option_id'])){
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => 'Option ID is missing!'
            ], 406);
        }

        $option = $this->container->option->getById($parameters['option_id']);
        $option_values = $this->option->getOptionValues($parameters['option_id']);

        foreach ($option_values as $option_value) {
            if ($option_value['image']) {
                $image = $option_value['image'];
            } else {
                $image = '';
            }

            $option_value_data[] = array(
                'option_value_id' => $option_value['option_value_id'],
                'name'            => html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8'),
                'image'           => $this->container['image']->resize($image, 50, 50),
            );
        }

        $sort_order = array();

        foreach ($option_value_data as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $option_value_data);

        $data[] = array(
            'option_id'     => $parameters['option_id'],
            'name'          => $option['name'],
            'type'          => $option['type'],
            'option_value'  => $option_value_data
        );

        return $response->withJson(['status' => 'ok', 'data' => $data]);
    }

    public function getOptionsType(Request $request, Response $response)
    {
        $types = $this->container->option->getTypes();
        return $response->withJson([
            'status' => $this->status[1],
            'data' => $types
        ]);
    }

    public function addOptionValues(Request $request, Response $response)
    {
        $parameters = $request->getParsedBody();

        $optionValues = $parameters['option_value'];

        $inputs = $rules = [];

        foreach ($optionValues as $optionValue) {
            foreach ($optionValue['option_value_description'] as $code => $description) {
                $inputs["{$code}_name"] = $description['name'];
                $rules["{$code}_name"] = 'string|required';
            }
        }

        $validator = $this->validator($inputs, $rules);

        if (!$validator) {
            return $response->withJson([
                'status' => 'error',
                'status_code' => $this->status[2],
                'error_description' => ''
            ], 406);
        }

        if ($createdOptionValues = $this->container->option->storeOptionValues($parameters))
        {
            $option = $this->container->option->getById($parameters['option_id']);
            $option_value_data = array();
            foreach ($createdOptionValues as $createdOptionValue)
            {
                $option_value_data[] = array(
                    'option_value_id' => $createdOptionValue,
                    'option_value_description' => $this->option->getOptionValueDescription($createdOptionValue),
                );
            }
            $data['product_option'][] = array(
                'option_id' => $parameters['option_id'],
                'name' => $option['name'],
                'type' => $option['type'],
                'option_description' => $option['option_description'],
                'product_option_value' => $option_value_data,
            );
            return $response->withJson([
                'status' => $this->status[1],
                'message' => 'Option added successfully',
                'data' => $data,
            ], 201);
        }

        return $response->withJson([
            'status' => 'error',
            'status_code' => $this->status[3],
            'error_description' => ''
        ], 400);

    }
	
}