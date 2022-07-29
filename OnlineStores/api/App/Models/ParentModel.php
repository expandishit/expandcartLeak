<?php

namespace Api\Models;

use Psr\Container\ContainerInterface;

abstract class ParentModel
{
    /**
     * The container instance
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * The database client instance
     * should be renamed to another meaningful name
     *
     * @var DB
     */
    protected $client;

    /**
     * The parent model constructor
     *
     * @param bool|\Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function __construct($container = null)
    {
        if ($container) {
            $this->setContainer($container);
        }
    }

    /**
     * The container setter
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * The client setter
     *
     * @param DB $client
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Prepare the internal system description data to be compatible with foriegns systems schema
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareProductDescriptionGet($data)
    {
        $newData = array();
        foreach ($data as $langId => $productDescription) {
            $newProductDescription = $productDescription;
            $langcode = $this->container->languageids["$langId"]['code'];
            $newProductDescription['language_id'] = "$langId";
            $newData[$langcode] = $newProductDescription;
        }

        return $newData;
    }

    /**
     * Prepare the foreign data to be compatible with internal system schema
     *
     * @param array $data
     *
     * @return array
     */
    protected function prepareProductDescriptionPost($data)
    {
        $newData = array();
        foreach ($data as $langCode => $productDescription) {
            $langid = $this->container->languagecodes[$langCode]['language_id'];
            $newData["$langid"] = $productDescription;
        }

        return $newData;
    }
}
