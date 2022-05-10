<?php

namespace Core\Controller;

use Db\Model\Field;

use Core\Response\JsonResponse;

class ApiCRUDController extends ModelController {
    public static $model = '';

    public static $serializer = '';

    /**
     * Method create
     * Binds to request method PUT
     * Creates and saves new instance of type Model
     *
     * @param $request
     */
    public static function create($request) {
        // Get Model fields
        $fields = static::getModel()::getFields();

        // Get Model fields values from request
        $model_fields = [];
        foreach ($fields as $field_name => $field) {
            // Fetch field from request with specified filters
            $value = static::getFieldValueFromRequest($request, $field_name, $field, true);
            if ($value !== null) {
                $model_fields[$field_name] = $value;
            }
        }

        // Init new instance of type Model
        $inst_name = static::getModel();
        $inst = new $inst_name();

        // Set instance values
        foreach ($model_fields as $field_name => $field_value) {
            $inst->$field_name = $field_value;
        }

        // Save instance to db
        $inst->save();

        // Return id
        JsonResponse::ok($inst->id);
    }

    /**
     * Method read
     * Binds to request method GET and path which ends with /%n
     * Calls `detail` method of Model's serializer
     * Reverts wrapped instance of model
     *
     * @param $request
     */
    public static function read($request) {
        // Get Model Id
        $id = $request['data']->get('id', ['required', 'int', 'positive']);

        // Fetch Model with specified Id
        $inst_name = static::getModel();
        $inst = $inst_name::get($id);

        // Serialize model
        $serializer = static::getSerializer();
        $serialized_model = $serializer::detail($inst);

        // Return serialized data
        JsonResponse::ok($serialized_model);
    }

    /**
     * Method list
     * Binds to request method GET
     * Calls `list` method of Model's serializer
     * Filters and Reverts wrapped instances of model
     *
     * @param $request
     */
    public static function list($request) {
        // TODO
        // Return paginated list of filtered models
    }

    /**
     * Method update
     * Binds to request method POST and path which ends with /%n
     * Updates instance and returns updated field => value
     *
     * @param $request
     */
    public static function update($request) {
        // Get Model Id
        $id = $request['data']->get('id', ['required', 'int', 'positive']);

        // Get Model fields
        $fields = static::getModel()::getFields();

        // Get Model fields values from request
        $model_fields = [];
        foreach ($fields as $field_name => $field) {
            // Fetch field from request with specified filters
            $value = static::getFieldValueFromRequest($request, $field_name, $field);
            if ($value !== null) {
                $model_fields[$field_name] = $value;
            }
        }

        // Fetch Model with specified Id
        $inst_name = static::getModel();
        $inst = $inst_name::get($id);

        // Set instance values
        $updated_fields = [];
        foreach ($model_fields as $field_name => $field_value) {
            if ($inst->$field_name !== $field_value) {
                $inst->$field_name = $field_value;
                $updated_fields[$field_name] = $field_value;
            }
        }

        // Save instance to db
        $inst->save();

        // Return updated fields
        JsonResponse::ok($updated_fields);
    }

    /**
     * Method delete
     * Binds to request method DELETE and path which ends with /%n
     * Deletes instance
     *
     * @param $request
     */
    public static function delete($request) {
        // Get Model Id
        $id = $request['data']->get('id', ['required', 'int', 'positive']);

        // Fetch Model with specified Id
        $inst_name = static::getModel();
        $inst = $inst_name::get($id);

        // Delete instance
        $inst->delete();

        // Return id
        JsonResponse::ok($inst->id);
    }

    /**
     * @param array $request
     * @param string $field_name
     * @param $field
     * @param bool $required
     *
     * @return mixed|null
     */
    protected static function getFieldValueFromRequest(array $request, string $field_name, $field,
        bool $required = false) {

        // Skip auto-defined fields
        if ($field instanceof Field\CreatedAtField ||
            $field instanceof Field\UpdatedAtField ||
            $field instanceof Field\DeletedAtField
        ) {

            return null;
        }

        // Set field filters
        $field_filters = $required ? ['required'] : [];
        if ($field instanceof Field\IntField) {
            $field_filters[] = 'int';
        }
        if ($field instanceof Field\IdField) {
            $field_filters[] = 'positive';
        }
        if ($field instanceof Field\CharField) {
            $field_filters['maxLen'] = $field->getLength();
        }
        if ($field instanceof Field\EmailField) {
            $field_filters[] = 'email';
        }

        // Fetch field from request with specified filters
        return $request['data']->get($field_name, $field_filters);
    }

    /**
     * @return string
     */
    private static function getModel(): string {
        return 'Models\\' . static::$model;
    }

    /**
     * @return string
     */
    private static function getSerializer(): string {
        return 'Api\\' . static::$serializer;
    }
}
