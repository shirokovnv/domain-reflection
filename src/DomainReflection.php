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

        $schema = $this->reflection->getModelSchema($model_class_name);

        $params = [
            'class_name' => $schema['name'],
            'table_name' => $schema['table_name'],
        ];

        $model = RefModel::where('class_name', $model_class_name)->first();
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
        /**
         * Initialize all domain models
         */
        foreach ($model_config as $config) {
            $model_class_collection = Helper::collectClassInfo($config['path'], $config['namespace']);

            foreach ($model_class_collection as $class_name) {
                $model = $this->reflectModelToDB($class_name);

                echo $model->class_name . " registered\r\n";
            }
        }

        /**
         * Synchronize ref_model_id for all relations
         */
        $ref_relations = RefRelation::all();
        foreach ($ref_relations as $ref_relation) {

            $output_str = "{{ sync_status }}Relation: " .
                $ref_relation->parent_class_name .
                "->" . $ref_relation->name . " {{ link_status }} " .
                $ref_relation->related_class_name;

            if ($this->syncRelatedModelId($ref_relation)) {
                $output_str = str_replace("{{ link_status }}", "linked to", $output_str);
                $output_str = str_replace("{{ sync_status }}", "", $output_str);
            } else {
                $output_str = str_replace("{{ link_status }}", "not linked to", $output_str);
                $output_str = str_replace("{{ sync_status }}", "Warning! ", $output_str);
            }

            echo $output_str . "\r\n";

        }

    }

    /**
     * Check if model exists in domain
     * @param string $model_class_name
     * @return mixed
     */
    public function modelExists(string $model_class_name)
    {
        return RefModel::where('class_name', $model_class_name)->exists();
    }

    /**
     * Removes information about specified model from database
     * @param string $model_class_name
     * @return mixed
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function removeReflection(string $model_class_name)
    {
        $ref_model = $this->findOrFailByClassName($model_class_name);

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
     * Searches for a specified model resource by class name
     * @param string $model_class_name
     * @return mixed
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFailByClassName(string $model_class_name)
    {
        return RefModel::with(['ref_fkeys', 'ref_fields', 'ref_relations'])
            ->where('class_name', $model_class_name)->firstOrFail();
    }

    /**
     * @param string $model_class_name
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function findByClassName(string $model_class_name) {
        return RefModel::with(['ref_fkeys', 'ref_fields', 'ref_relations'])
            ->where('class_name', $model_class_name)->first();
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
                'keys' => $relation['keys'],
                'parent_class_name' => $relation['parent_class_name'],
                'related_model_id' => null,
                'related_class_name' => $relation['related_class_name']
            ];

            if (!$ref_relation) {
                $ref_relation = RefRelation::create($params);
            } else {
                $ref_relation->update($params);
            }

            $this->syncRelatedModelId($ref_relation);
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
     * Synchronize related_model_id for specific relation
     * @param $ref_relation
     * @return int|null
     */
    private function syncRelatedModelId(&$ref_relation) {
        $ref_model = $this->findByClassName($ref_relation->related_class_name);

        if ($ref_model) {
            $ref_relation->update([
                'related_model_id' => $ref_model->id
            ]);

            return $ref_model->id;
        }

        return null;
    }
}
