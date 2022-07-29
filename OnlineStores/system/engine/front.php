<?php
final class Front {
	protected $registry;
	protected $pre_action = array();
	protected $error;
	
	public function __construct($registry) {
		$this->registry = $registry;
		$registry->set('Amazonconnector', new Amazonconnector($registry));
	}
	
	public function addPreAction($pre_action) {
		$this->pre_action[] = $pre_action;
	}

	/**
	 * We are sending a custom headers in some endpoints which uses the combined
	 * json submitter, this function is validating that only the request that
	 * has this custom header does not applied on any other routes exept only
	 * the allowed routes which is only Admin > Product > [insert, update]
	 * and Shipping > Category based > Save settings
	 *
	 * @warning : this should not be updated at any mean without a confirmation
	 *
	 * @param \Action $action
	 *
	 * @return bool
	 */
	private function customHeaderValidation($action)
	{
		if (!IS_ADMIN) {
			return true;
		}

		$allowedCombinedFiles = [
			'product.php' => [
				'Controllercatalogproduct' => ['update' => true, 'insert' => true],
			],
			'category_product_based.php' => [
				'Controllershippingcategoryproductbased' => ['saveSettings' => true]
			],
		];

		$allowedSecuredFiles = [
			'store_scripts.php' => [
				'Controllersettingstorescripts' => ['save' => true]
			],
		    'manufacturer.php' =>[
		        'Controllercatalogmanufacturer' => ['update' => true, 'insert' => true],
		    ],
			'information.php' =>[
		        'Controllercataloginformation' => ['update' => true, 'insert' => true],
		    ],
		    'category.php'=>[
		        'Controllercatalogcategory' =>['update' => true, 'insert' => true]
		    ],
			'custom_email_templates.php'=>[
				'Controllermodulecustomemailtemplates' => ['edit' => true]
			],
			'customize.php' => [
				'Controllertemplatescustomize' => ['putFileContents' => true]
			],
            'smshare.php' => [
                'Controllermodulesmshare' => ['index' => true]
            ],
            'bank_transfer.php' => [
                'Controllerpaymentbanktransfer' => ['index' => true]

            ],
//            'exceptions.php' => [
//                'Controllererrorexceptions' => ['index' => true]
//            ]
		];

		if (isset($_SERVER['HTTP_X_EC_FORM_INPUTS']) && $_SERVER['HTTP_X_EC_FORM_INPUTS'] === 'COMBINED') {
			if (!isset($allowedCombinedFiles[basename($action->getFile())])) {
				return false;
			}

			if (!isset($allowedCombinedFiles[basename($action->getFile())][$action->getClass()])) {
				return false;
			}

			if (!isset($allowedCombinedFiles[basename($action->getFile())][$action->getClass()][$action->getMethod()])) {
				return false;
			}
		}

		if (isset($_SERVER['HTTP_X_EC_FORM_INPUTS']) && $_SERVER['HTTP_X_EC_FORM_INPUTS'] === 'SECURED') {
			if (!isset($allowedSecuredFiles[basename($action->getFile())])) {
				return false;
			}

			if (!isset($allowedSecuredFiles[basename($action->getFile())][$action->getClass()])) {
				return false;
			}

			if (!isset($allowedSecuredFiles[basename($action->getFile())][$action->getClass()][$action->getMethod()])) {
				return false;
			}
		}

		return true;
	}

	public function dispatch($action, $error) {
		if ($this->customHeaderValidation($action) == false) {
			throw new \Exception('invalid request header!!');
		}

		$this->error = $error;
			
		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);
					
			if ($result) {
				$action = $result;
				
				break;
			}
		}
			
		while ($action) {
			$action = $this->execute($action);
		}
  	}
    
	private function execute($action) {
		if (file_exists($action->getFile())) {
			require_once($action->getFile());
			
			$class = $action->getClass();

			$controller = new $class($this->registry);
			
			if (is_callable(array($controller, $action->getMethod()))) {
				$action = call_user_func_array(array($controller, $action->getMethod()), $action->getArgs());
			} else {
				$action = $this->error;
			
				$this->error = '';
			}
		} else {
			$action = $this->error;
			
			$this->error = '';
		}
		
		return $action;
	}
}
?>