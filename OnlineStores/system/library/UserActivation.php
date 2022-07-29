<?php


class UserActivation
{
    private $registry;
    private $config;
    private $settingModel;

    /**
     * @param $registry
     */
    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->loader = $this->registry->get('load');
        $this->config = $this->registry->get('config');
        $this->settingModel = $this->loader->model('setting/setting', ['return' => true]);
    }

    /*
        ( url ?p=New_User )
        The new parameter is case sensitive so add it as is in the specs
        The new parameter should be only shown only one time once they land on the dashboard page for the first time and once they [refresh the page/exit page/browse to another page/logged out and then logged in again] The parameter shouldn’t be shown again on the dashboard page except for the first time
        If the user logged out without completing the questionnaire and logged in again he can view the new parameter normally once he lands on the dashboard page
     */
    public const  NEW_USER = 'New_User';

    /*
        ( url ?p=SActivation )
        the  parameter should be shown once a user completes all of these 3 actions without a specific order.
        The only condition is that it should be shown once all 3 are done.
    */
    public const SOFT_ACTIVATION = 'SActivation';

    /*
        ( url ?p=HActivation )
        we can define that once he edits the order status to something other than the “Pending Status
       ”for the first time for an actual order, not a test order

     */
    public const HARD_ACTIVATION = 'HActivation';


    private const REDIRECT_WITH_USER_ACTIVATION = 'redirectWithUserActivation';



    // actions
    public const ADD_PRODUCT = 'add_product';
    public const LINK_DOMAIN = 'link_domain';
    public const PUBLISH_TEMPLATE = 'publish_template';

    // user Activation Types
    private const USER_ACTIVATION_TYPES = [
        UserActivation::SOFT_ACTIVATION,
        UserActivation::HARD_ACTIVATION,
        UserActivation::NEW_USER
    ];


    // soft Activation Actions
    private const SOFT_ACTIVATION_ACTIONS = [
        self::ADD_PRODUCT,
        self::LINK_DOMAIN,
        self::PUBLISH_TEMPLATE
    ];

    private const STEPS_COMPLETED = 'steps_completed';


    public function getUserActivationParameter($type): string
    {
        return 'p=' . $type;
    }

    /**
     * @return array
     */
    private function _getSoftActivationDefaultActions(): array
    {
        $defaults = [];
        foreach (self::SOFT_ACTIVATION_ACTIONS as $action) {
            $defaults[$action] = false;
        }
        return $defaults;
    }

    /**
     * @param bool|null $val
     * @param bool|null $redirectParam
     */
    public function setHardActivation(?bool $val = true, ?bool $redirectParam = true): void
    {
        $userActivationConfig = [self::HARD_ACTIVATION => $val];
        if ($redirectParam)
            $userActivationConfig[self::REDIRECT_WITH_USER_ACTIVATION] =
                $this->getUserActivationParameter(self::HARD_ACTIVATION);
        $this
            ->loader->model('setting/setting', ['return' => true])
            ->insertUpdateSetting('config', $userActivationConfig);
    }

    public function setRedirectWithUserActivationParamToConfig(string $type): void
    {
        $setting = ($this->settingModel) ?: $this->loader->model('setting/setting', ['return' => true]);
        $param = $this->getUserActivationParameter($type);
        $setting
            ->insertUpdateSetting('config', [self::REDIRECT_WITH_USER_ACTIVATION => $param]);
    }

    /**
     * @return bool|null
     */
    public function isoftActivationExists(): ?bool
    {
        return (bool)$this->config->get(self::SOFT_ACTIVATION);
    }

    /**
     * @param array $config
     */
    private function _setSoftActivationConfig(array $config = []): void
    {
        $setting = ($this->settingModel) ?: $this->loader->model('setting/setting', ['return' => true]);

        $softActivationDefaultActions = self::_getSoftActivationDefaultActions();

        $softActivationDefaultActions[self::STEPS_COMPLETED] = 0;

        $softActivationConfig = [self::SOFT_ACTIVATION => $config ?: $softActivationDefaultActions];

        $setting->insertUpdateSetting('config', $softActivationConfig);

        $this->config->set('config', $softActivationConfig);
    }


    private function _getSoftActivation()
    {
        return ($this->config->get(self::SOFT_ACTIVATION));
    }

    /**
     * @return bool|null
     */
    public function isSoftActivationCompleted() :?bool
    {
        $stepsCompleted = $this->_getSoftActivation()[self::STEPS_COMPLETED] ?? 0;

        return ($stepsCompleted == count(self::SOFT_ACTIVATION_ACTIONS));
    }


    /**
     * @param string|null $action
     */
    private function _setSoftActivationAction(?string $action): void
    {
        $softActivationArr = $this->_getSoftActivation();
        // check if the Action valid or not
        if (!$softActivationArr || !in_array($action, self::SOFT_ACTIVATION_ACTIONS))
            return;


        $softActivationArr[$action] = true;
        $softActivationArr[self::STEPS_COMPLETED] += 1;

        $this->_setSoftActivationConfig($softActivationArr);

    }

    /**
     * @param string|null $action
     * @return bool|null
     */
    private function _isActionFiredBefore(?string $action): ?bool
    {
        $config = $this->_getSoftActivation();

        return
            (
                $this->isSoftActivationCompleted()
                ||
                (isset($config[$action]) && $config[$action])
            );
    }

    /**
     * @return bool|null
     */
    public function isHardActivationCompleted(): ?bool
    {
        return (bool)($this->config->get(self::HARD_ACTIVATION));
    }

    /**
     * @param string|null $action
     */
    public function processSoftActivation(?string $action): void
    {
        // if softAction config does not exists then  add it
        if (!$this->isoftActivationExists())
            $this->_setSoftActivationConfig();
        // check if action has been fired before
        if ($this->_isActionFiredBefore($action))
            return;

        // add the action
        $this->_setSoftActivationAction($action);

        // if isSoftActivation steps has been completed
        // then add the redirect parameter
        if ($this->isSoftActivationCompleted())
            $this->setRedirectWithUserActivationParamToConfig(self::SOFT_ACTIVATION);

    }

    /**
     * @param $order
     * @param array $orderProducts
     * @return bool|null
     */
    public function isTestOrder($order, array $orderProducts = []): ?bool
    {
         $checkoutOrderModel = $this->loader->model('checkout/order', ['return' => true] , DIR_CATALOG);
         return $checkoutOrderModel->isTestOrder($order , $orderProducts);
    }


    /**
     * @param string $param
     * @param string $userActivationType
     * @return bool|null
     */
    public function isParamTypeOFUserActivation($param ='' , $userActivationType = ''): ?bool
    {
         return (strpos($param , $userActivationType) !== false);
    }


    /**
     * @param $param
     * @return string|null
     */
    public function getTypeOfRedirectParam($param): ?string
    {
        $result = null;

        foreach (self::USER_ACTIVATION_TYPES as $action) {
            if ($this->isParamTypeOFUserActivation($param, $action)) {
                $result = $action;
                break;
            }
        }
        return $result;
    }




}