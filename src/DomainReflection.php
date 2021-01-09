<?php

namespace Shirokovnv\DomainReflection;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Shirokovnv\DomainReflection\Models\RefModel;
use Shirokovnv\DomainReflection\Models\RefField;
use Shirokovnv\DomainReflection\Models\RefRelation;
use Shirokovnv\DomainReflection\Models\RefFkey;
use Shirokovnv\DomainReflection\Models\RefScope;
use Shirokovnv\DomainReflection\Models\RefScopeArg;
use Shirokovnv\DomainReflection\Utils\Helper;
use Shirokovnv\ModelReflection\ModelReflection;

/**
 * Class DomainReflection
 * @package Shirokovnv\DomainReflection
 */
class DomainReflection
{
    /**
     * @var ModelReflection
     */
    private $reflection;

    /**
     * DomainReflection constructor.
     * @param ModelReflection $reflection
     */
    public function __construct(ModelReflection $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * Saves meta information about specified model in database
     * @param string $model_class_name
     * @return mixed
     * @throws \Shirokovnv\ModelReflection\Exceptions\UnknownRelTypeException
     */
    public function reflectModelToDB(string $model_class_name)
    {

        $domain_name = $this->makeDomainName($model_class_name);
        $schema = $this->reflection->getModelSchema($model_class_name);

        $params = [
            'domain_name' => $domain_name,
            'class_name' => $schema['name'],
            'table_name' => $schema['table_name'],
        ];

        $model = RefModel::where('domain_name', $domain_name)->first();
        if ($model) {
            $model->update($params);
        } else {
            $model = RefModel::create($params);
        }

        $this->syncModelFields($model, $schema['fields']);
        $this->syncModelRelations($model, $schema['relations']);
        $this->syncModelForeignKeys($model, $schema['foreign_keys']);
        $this->syncModelScopes($model, $schema['scopes']);

        return $model;

    }

    /**
     * Register in database all domain models by paths, specified in domain-reflection config
     * @throws \Shirokovnv\ModelReflection\Exceptions\UnknownRelTypeException
     */
    public function registerDomainModels()
    {

        $model_config = config('domain-reflection.models');

        foreach ($model_config as $config) {
            $model_class_collection = Helper::collectClassInfo($config['path'], $config['namespace']);

            foreach ($model_class_collection as $class_name) {
                $model = $this->reflectModelToDB($class_name);

                echo $model->domain_name . " registered\r\n";
            }
        }

    }

    /**
     * Check if model exists in domain
     * @param string $model_domain_name
     * @return mixed
     */
    public function modelExists(string $model_domain_name)
    {
        return RefModel::where('domain_name', $model_domain_name)->exists();
    }

    /**
     * Removes information about specified model from database
     * @param string $model_domain_name
     * @return mixed
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeReflection(string $model_domain_name)
    {
        $ref_model = $this->findByClassName($model_domain_name);

        $ref_model->ref_fkeys()->each(function ($item, $key) {
            $item->delete();
        });
        $ref_model->ref_relations()->each(function ($item, $key) {
            $item->delete();
        });
        $ref_model->ref_fields()->each(function ($item, $key) {
            $item->delete();
        });

        return $ref_model->delete();

    }

    /**
     * Searches for the specified model resource by domain name
     * @param string $model_domain_name
     * @return mixed
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByDomainName(string $model_domain_name)
    {
        return RefModel::with(['ref_fkeys', 'ref_fields', 'ref_relations'])
            ->where('domain_name', $model_domain_name)->firstOrFail();
    }

    /**
     * Searches for a specified model resource by class name
     * @param string $model_class_name
     * @return mixed
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByClassName(string $model_class_name)
    {
        $domain_name = $this->makeDomainName($model_class_name);
        return $this->findByDomainName($domain_name);
    }

    /**
     * Synchronize information about model fields (e.g. after migrations and other changes)
     * @param $ref_model
     * @param $array_of_fields
     */
    private function syncModelFields(&$ref_model, &$array_of_fields)
    {

        foreach ($array_of_fields as $field) {
            $ref_field = $ref_model->ref_fields()->where('name', $field['name'])->first();

            $params = [
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'fillable' => $field['fillable'],
                'guarded' => $field['guarded'],
                'hidden' => $field['hidden'],
                'required' => $field['required'],
                'ref_model_id' => $ref_model->id
            ];

            if (!$ref_field) {
                $ref_field = RefField::create($params);
            } else {
                $ref_field->update($params);
            }
        }

        // ensure we don't have deleted fields
        $ref_fields = $ref_model->ref_fields()->get();
        foreach ($ref_fields as $field) {

            if (!in_array($field->name, Arr::pluck($array_of_fields, 'name'))) {
                $field->delete();
            }

        }

    }

    /**
     * Synchronize information about model relations
     * @param $ref_model
     * @param $array_of_relations
     */
    private function syncModelRelations(&$ref_model, &$array_of_relations)
    {
        foreach ($array_of_relations as $relation) {
            $ref_relation = $ref_model->ref_relations()->where('name', $relation['name'])->first();

            $params = [
                'name' => $relation['name'],
                'ref_model_id' => $ref_model->id,
                'type' => $relation['type'],
                'keys' => $relation['keys']
            ];

            if (!$ref_relation) {
                $ref_relation = RefRelation::create($params);
            } else {
                $ref_relation->update($params);
            }
        }

        // ensure we don't have deleted relations
        $ref_relations = $ref_model->ref_relations()->get();
        foreach ($ref_relations as $relation) {

            if (!in_array($relation->name, Arr::pluck($array_of_relations, 'name'))) {
                $relation->delete();
            }

        }
    }

    /**
     * Synchronize information about model table foreign keys
     * @param $ref_model
     * @param $array_of_fkeys
     */
    private function syncModelForeignKeys(&$ref_model, &$array_of_fkeys)
    {

        foreach ($array_of_fkeys as $fkey) {

            $ref_field = $ref_model->ref_fields()->where('name', $fkey['name'])->firstOrFail();

            $ref_fkey = $ref_field->ref_fkey()->first();

            $params = [
                'ref_field_id' => $ref_field->id,
                'name' => $fkey['name'],
                'foreign_table' => $fkey['foreign_table'],
                'references' => $fkey['references']
            ];

            if (!$ref_fkey) {
                $ref_fkey = RefFkey::create($params);
            } else {
                $ref_fkey->update($params);
            }

        }

        // ensure we don't have deleted foreign keys
        $ref_fkeys = $ref_model->ref_fkeys()->get();
        foreach ($ref_fkeys as $fkey) {

            if (!in_array($fkey->name, Arr::pluck($array_of_fkeys, 'name'))) {
                $fkey->delete();
            }

        }
    }

    /**
     * Synchronize information about scopes of specific model
     * @param $ref_model
     * @param $array_of_scopes
     */
    private function syncModelScopes(&$ref_model, &$array_of_scopes) {

        foreach ($array_of_scopes as $scope) {
            $ref_scope = $ref_model->ref_scopes()->where('name', $scope['name'])->first();

            $params = [
                'name' => $scope['name'],
                'ref_model_id' => $ref_model->id
            ];

            if (!$ref_scope) {
                $ref_scope = RefScope::create($params);
            } else {
                $ref_scope->update($params);
            }

            foreach ($scope['args'] as $scope_arg) {
                $ref_scope_arg = $ref_scope->ref_scope_args()
                    ->where('name', $scope_arg['name'])
                    ->first();

                $arg_params = [
                    'name' => $scope_arg['name'],
                    'position' => $scope_arg['position'],
                    'isOptional' => $scope_arg['isOptional'],
                    'typeHint' => $scope_arg['typeHint'],
                    'ref_scope_id' => $ref_scope->id
                ];

                if (!$ref_scope_arg) {
                    $ref_scope_arg = RefScopeArg::create($arg_params);
                } else {
                    $ref_scope_arg->update($arg_params);
                }
            }

        }

        // ensure we don't have deleted scopes
        $ref_scopes = $ref_model->ref_scopes()->get();
        foreach ($ref_scopes as $scope) {

            if (!in_array($scope->name, Arr::pluck($array_of_scopes, 'name'))) {
                $scope->delete();
            }

        }

    }

    /**
     * Transforms class name to unified domain name
     * e.g. App\Models\User -> app.models.user
     * @param string $class_name
     * @return string
     */
    private function makeDomainName(string $class_name)
    {

        $name_components = explode("\\", $class_name);
        $collection = collect($name_components);

        return $collection->map(function ($item) {
            return Str::snake($item);
        })->implode(".");
    }
}
