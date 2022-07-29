<?php

use \Swaggest\JsonSchema\Schema;
use \Swaggest\JsonSchema\InvalidValue;

class ModelTemplatesSchema extends ModelTemplatesTemplate
{
    /**
     * @var string
     */
    protected $schemaPath;

    /**
     * @var array
     */
    protected $schemas = [
        'base' => HTTP_SERVER . 'model/templates/specs/base-template-schema.json',
        'sections' => HTTP_SERVER . 'model/templates/specs/sections-schema.json',
    ];

    /**
     * Set the schema path.
     *
     * @param string $schema
     *
     * @return $this
     */
    public function setSchemaPath($schema)
    {
        $this->schemaPath = $this->schemas[$schema];

        return $this;
    }

    /**
     * Get the schema path.
     *
     * @return string
     */
    public function getSchemaPath()
    {
        return $this->schemaPath;
    }

    /**
     * Get schema contents.
     *
     * @param string $path
     *
     * @return string
     */
    public function importSchema($path)
    {
        return file_get_contents($path);
    }

    /**
     * Factory method to run a schema gaurd to check the schema file.
     *
     * @param string $path
     *
     * return array|bool
     */
    public function gaurd(&$contents)
    {
        try {
            $schema = Schema::import(
                $this->decode(
                    $this->importSchema($this->getSchemaPath())
                )
            );

            $schema->in(
                $this->decode($contents)
            );

            return true;
        } catch (InvalidValue $e) {

            $errors = $e->inspect();

            preg_match('#((?=.*?\:.*?data)(.*)\,|(.*?))#U', $errors->error, $error);

            $this->setError([
                'detailed_error' => $errors->error,
                'error' => rtrim($error[1], ','),
                'position' => $errors->dataPointer
            ]);

            unset($errors);
            return false;
        }
    }

    /**
     * Decodes the given json.
     *
     * @param string $contents
     * @param bool $array
     *
     * @return array|null
     */
    protected function decode($contents, $array = false)
    {
        return json_decode($contents, $array);
    }

    /**
     * Return all sections schemas.
     *
     * @param array $schema
     *
     * @return array
     */
    public function getSectionSchemas($schema)
    {
        $sections = [];

        foreach ($schema->pages as $pageKey => $pages) {

            foreach ($pages->regions as $regionKey => $region) {
                $sections = array_merge($sections, $region->sections);
            }

        }

        return $sections;
    }
}
