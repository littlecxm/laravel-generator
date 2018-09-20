<?php

namespace InfyOm\Generator\Generators\API;

use InfyOm\Generator\Common\CommandData;
use InfyOm\Generator\Generators\BaseGenerator;
use InfyOm\Generator\Utils\FileUtil;
use InfyOm\Generator\Utils\TableFieldsGenerator;

class APIResourceGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $resourceFileName;

    /** @var string */
    private $collectionFileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathApiResource;
        $this->resourceFileName = $this->commandData->modelName.'APIResource.php';
        $this->collectionFileName = $this->commandData->modelName.'APICollection.php';
    }

    public function generate()
    {
        $this->generateResource();
        $this->generateResourceCollection();
    }

    public function generateResource()
    {
        $templateData = get_template('api.resource.api_resource', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = str_replace('$CAST$', implode(','.infy_nl_tab(1, 2), $this->generateCasts()), $templateData);

        FileUtil::createFile($this->path, $this->resourceFileName, $templateData);

        $this->commandData->commandComment("\nResource created: ");
        $this->commandData->commandInfo($this->resourceFileName);
    }

    public function generateResourceCollection()
    {
        $templateData = get_template('api.resource.api_collection', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->collectionFileName, $templateData);

        $this->commandData->commandComment("\nResource Collection created: ");
        $this->commandData->commandInfo($this->collectionFileName);
    }

    public function generateCasts()
    {
        $casts = [];

        $timestamps = TableFieldsGenerator::getTimestampFieldNames();

        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, $timestamps)) {
                continue;
            }

            $rule = "'".$field->name."' => ";

            switch ($field->fieldType) {
                case 'integer':
                    $rule .= "'integer'";
                    break;
                case 'double':
                    $rule .= "'double'";
                    break;
                case 'float':
                    $rule .= "'float'";
                    break;
                case 'boolean':
                    $rule .= "'boolean'";
                    break;
                case 'dateTime':
                case 'dateTimeTz':
                    $rule .= "'datetime'";
                    break;
                case 'date':
                    $rule .= "'date'";
                    break;
                case 'enum':
                case 'string':
                case 'char':
                case 'text':
                    $rule .= "'string'";
                    break;
                default:
                    $rule = '';
                    break;
            }

            if (!empty($rule)) {
                $casts[] = $rule;
            }
        }

        return $casts;
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->resourceFileName)) {
            $this->commandData->commandComment('Create API Resource file deleted: '.$this->resourceFileName);
        }

        if ($this->rollbackFile($this->path, $this->collectionFileName)) {
            $this->commandData->commandComment('Update API Collection file deleted: '.$this->collectionFileName);
        }
    }
}
