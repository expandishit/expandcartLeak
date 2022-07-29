<?php

namespace Api\Models\Setting;

use Psr\Container\ContainerInterface;

class Domain
{
    /**
     * @var
     */
    private $load;
    /**
     * @var
     */
    private $registry;
    /**
     * @var
     */
    private $languagecodes;
    /**
     * @var
     */
    private $languageids;
    /**
     * @var
     */
    private $config;

    /**
     * Domain constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->registry = $container['registry'];
        $this->config = $container['config'];
        $this->load = $container['loader'];
        $this->languagecodes = $container['languagecodes'];
        $this->languageids = $container['languageids'];
    }

    /**
     * @return mixed
     */
    public function getDomains()
    {
        $this->load->model('setting/domainsetting');
        return $this->registry->get('model_setting_domainsetting')->getDomains();
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $this->load->model('setting/domainsetting');
        if ($this->registry->get('model_setting_domainsetting')->deleteDomain($id))
            return true;
        return false;
    }

    /**
     * @return mixed
     */
    public function countDomain($domain=null)
    {
        $this->load->model('setting/domainsetting');
        return $this->registry->get('model_setting_domainsetting')->countDomain($domain);
    }

    /**
     * @return int
     */
    public function dedicatedDomain()
    {
        $this->load->model('module/dedicated_domains/domains');
        return $domain_limit = $this->registry->get('model_module_dedicated_domains_domains')->isActive() ? 10 : 3;
    }

    /**
     * @param $domain
     * @return mixed
     */
    public function addDomain($domain)
    {
        $this->load->model('setting/domainsetting');
        return $this->registry->get('model_setting_domainsetting')->addDomain($domain);
    }

    /**
     * @param $domain
     * @return bool
     */
    public function setMixpanel($domain)
    {
        $this->load->model('setting/mixpanel');
        $this->registry->get('model_setting_mixpanel')->trackEvent('Link Domain',['Domain Name' => $domain]);
        return true;
    }

    /**
     * @param $domain
     * @return bool
     */
    public function setAmplitude($domain)
    {
        $this->load->model('setting/amplitude');
        $this->registry->get('model_setting_amplitude')->trackEvent('Link Domain',['Domain Name' => $domain]);
        return true;
    }
}
