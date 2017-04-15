# didbot-api
[![Build Status](https://travis-ci.org/didbot/didbot-api.svg?branch=master)](https://travis-ci.org/didbot/didbot-api)
API for [didbot.com](https://didbot.com) or a self hosted [didbot](https://github.com/didbot/didbot) application.

##  Installation

Install the package via composer:
```bash
composer require didbot/didbot-api
```

Add the service provider to the config/app.php providers array
```php
// config/app.php
'providers' => [
    ...
    Didbot\DidbotApi\ApiServiceProvider::class
];
```

Finally add the Didbot\DidbotApi\Traits\HasDids trait to the User model.

```php
use Didbot\DidbotApi\Traits\HasDids;

class User
{
    use HasDids;

    // ...
}
```


## Database
To take advantage of full text search the recommended database is postgresql.