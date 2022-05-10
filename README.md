## NFT Stack Back-end

#### TODO list
- Implement default list and retrieve serializers for CRUD Controller
- Implement
    - pagination,
    - filters,
    - and sorting for default list controller

---

#### Usage
1. Run `git clone {path} && cd {repo_name}`
2. Create database
3. Pass sensitive data with env variable or create loc.{config_file_name} and rewrite variables
4. Make migrations `php Manage.php -c migrate`
5. Redirect all requests to index.php by frontend web server(Apache, nginx, php-fpm etc.)
5. Open `/api/v1/ping` to check if app was successfully installed

#### Requirements
- PHP 7.2+

---

#### Methodology
###### "MMSc" Deisgn Pattern

There are three main components from which backend server is composed.
1. Model - implements interaction with db and some encapsulated model functionality 
2. Module - implements interactions and compositions of several models
3. Serializer - serializes/wraps output of model or module 

* And Controller which just calls Model or Module and wraps output in a Serializer.
It is like a combine which calls components in a right order and returns result.

To avoid "Spaghetti" code follow this mini-guide

- 0 means Column-component can not be imported* in Raw-component
- 1 means Column-component can be imported* in Raw-component

|#|Model|Module|Serializer|Controller|
|:---:|:---:|:---:|:---:|:---:|
|Model|0|1|0|1|
|Module|0|1|0|1|
|Serializer|0|0|1|1|
|Controller|0|0|0|0|

* "imported" means an instance of type A can not be initialized in an instance of type B

---

#### Request Processing Principle
|Request lifecycle|Procedure call|Example|
|:---:|:---:|:---:|
|1. Matching route|-|-|
|2. Parsing data|Engine\Parser|-|
|3. Applying Middlewares|interface Core\Middleware|CORS, Localization, Auth, etc.|
|4. Exec Controller|Callable|function/method|
|5. Serializing(Wrapping) response|-|-|
|6. Returning response|interface Core\Response|JsonResponse, HttpResponse|

---

#### MiniDoc
- Model
    - Each model should
        - be extended from `Core\Db\Model`
        - have `$table_name`
        - implement function `fields()` returning array of model fields
    - Fields
        - verifies values before writing to db
        - available fields `CharField` `IntField`
        - custom fields make sure that no invalid data will be written to the particular model, eg 
            - `IdField`
            - `LoginField`
            - `EmailField`
        - Special Fields
            - `CreatedAtField`
            - `UpdatedAtField`
            - `DeletedAtField`
        - Autofilled Fields
            - `RandomHashField`
            - `RandomTokenField`
- Routing
    - Route: `Route::{method}('{path}', '{controller_namespace}@{controller}');`
    - Group: `Route::group('{preifx}', ...{Route|Route::group});`
    - Apply middleware `->middleware('{name}');`
    - Apply middleware on group `Route::groupMiddleware('{name}', array);`
- Controller
    - Generics
        - `ApiCRUDController` Binds model default functionality to routes, supports 5 methods
            1. `::read => GET /%n`
            2. `::list => GET`
            3. `::create => PUT`
            4. `::update => POST /%n`
            5. `::delete => DELETE /%n`
    - `$request` is passed to each controller
    - Get field value `$request['data']->get('{field}', array {filters});`
        - Filters `required, default, int, double, positive, maxLen, minLen, oneOf, etc.`
    - Get auth user `$request['user']`
- Command
    - Entry point Manage.py `php Manage.php -c {command}`
    - `migrate` initialises database and syncs migrations, supports `--prefix`
    - `migration` creates new file to write current database changes and stores it in `Migrations\\` dir, required argument `--name`, supports `--prefix` to store grouped migrations 
- Exceptions
    - Each component has its exceptions inside its `Exception` dir