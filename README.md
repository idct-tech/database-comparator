database-comparator
===================

Basic framework for database comparisons. Allows to report differences between
two datasources.

Useful especially when making a major change in an application which writes data
to any data source and you would like to verify if nothing got broken. One could
say that it allows to write easy black box tests.

contents
========

Framework provides so far:
* `PdoSource`: for comparing databases to which PDO can be used as a connector.
* `SourceInterface`: interface for writing new compatible data source connectors.
* `SimpleOutput`: basic textual output for writing out differences to files.
* `OutputInterface`: interface for building new output writing objects.

usage
=====

1. Install by composer using:
```bash
composer require idct/database-comparator="^1.0"
```

or manually by downloading contents and placing in desired folder.

2. Include all your files or the autoloader:
```php
include "vendor/autoload.php";
```

3. Build your comparison definitions:

Assuming you have a table with schema:

|id|irrelevant|value|
|--|----------|-----|
|0|somedate|11|
|0|somedate|12|

Create new objects:
```php
$comparator = new Compare();
$objects = new PdoSource();
$pdo = new PDO(...); //your database connection details here
```

In case you compare within SAME database you can use same connector (same source)
```php
$objects->setPdo($pdo)
        ->setQueryAll('select * from some_table limit :limit offset :offset') //limit and offset will be dynamically updated
        ->setQuerySingle('select * from some_table where {_keys}')
        ->setSingleKeys(['id']);

$comparator->addSource('main', $objects)
           ->addSource('test', $objects); /* in this case we shall use the same
           source as for left calls it will use queryAll and for right ones
           querySingle */

$output = (new SimpleOutput())
          ->setBaseFilename('somepath/comparison_{source}.log');
          // {source} token will be dynamically replaced

$comparator->setOutput($output)
           ->run();

//report differences
var_dump($comparator->getDiffsCount());
```

4. Special cases:

In some cases you want to omit some fields - for instance if your tables have the
automatic "last updated" fields. You can do it in two ways:
* do not specify it within your queries (`QueryAll`, `QuerySingle`)
or in case it would be hard to do (like in SQL you would need to list all other
fields) set "ignored fields":
```php
$objects->setIgnoredFields(['last_updated']);
```

In case your tables / data sources have complex data sources just specify them as
next array elements in `setSingleKeys`, for example:
```php
$objects->setSingleKeys(['id','sub_id']);
```

One of the most interesting features of the framework is option to alter results
just before comparison - this is very useful in situations when you *know* what
you have changed and want to check if you achieved it.
For instance: if you had an application which fills the sample table above and
you made a change to your app which causes writing of the `value` field with data
substracted by 2 you can still verify that using `getSinglePreCheckTransformation`
method.

```php
$objects->setSinglePreCheckTransformation(function($their, $mine) {
    $mine['value'] += 2;
    return $mine;
});
```

`SinglePreCheckTransformation` is meant to update the data from the current data
source and should return the updated set. So in case your application substracted
2 from value of field `value` of every row/entry you can add 2 back in order to
make the comparison using the code above.

contribution
============

In case you found any bugs, problems or would like to add some features... or
write tests :) it is more than welcome! Please make the changes, add a pull
request and I will merge it when it is possible. Thank you in advance!