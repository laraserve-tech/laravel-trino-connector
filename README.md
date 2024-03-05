# Laravel Trino Connector

Trino is an ANSI SQL compliant query engine, that works with BI tools such as R, Tableau, Power BI, Superset and many others.

## Installation

### With Composer
```bash
composer require LaraserveTech/laravel-trino-connector
```

Once installed, run the following to publish the config file:
```
php artisan vendor:publish --tag=trino-connector-config
```

Open the `./config/trino-connector.php` file, and update the `base_url` and `auth_token` needed to connect to your Trino instance.

Note, the `auth_token` needs to be a base64 encoded string of the username and password in the format: `user:password`.


## Requirements
 PHP >= 8.1

Laravel >= 9.0
 
## Usage

```php
$trino = new Trino();
$trino->execute("SELECT * FROM store.database.table LIMIT 1");

// Check  if the query was successful
if (!$trino->success()) {
    return $trino->getErrors();
}

// Get the results of the query. The data is returned as an array, for easy processing
$data = $trino->getData();

collect($data)->each(function ($row) {
    // Process the $row
});
```

## License
[MIT LICENSE](LICENSE)

## Contribution

To contribute to this software, please make a pull request, and stick to the naming convention and coding standards.

## Contact
For questions, reach out to [support@laraserve.com](mailto:support@laraserve.com).