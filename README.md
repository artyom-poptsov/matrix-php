# Matrix-PHP
A simple library that allows to interact with a Matrix instance through the API.

![PHP Composer](https://github.com/artyom-poptsov/matrix-php/workflows/PHP%20Composer/badge.svg)

## License
Matrix-PHP is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version. Please see `LICENSE` file for the terms of GNU General Public License.

## Dependencies
- curl

## Installation
### Manually (on Ubuntu)
```
$ sudo apt install php-curl
$ git clone https://github.com/artyom-poptsov/matrix-php.git
```

### By Composer
Create `composer.json` in your project directory (or add dependencies to it):
```
{
    "require": {
        "artyom-poptsov/matrix-php": "dev-master"
    },
    "repositories": [
        {
            "type":  "git",
            "url":   "https://github.com/artyom-poptsov/matrix-php"
        }
    ]
}
```

Then execute:
```
$ composer install
```

## Syncing with the upstream
```
$ composer update
```

## Testing
```
$ ./vendor/phpunit/phpunit/phpunit --testdox tests
```

## Examples
### Account creation
```
require_once('vendor/artyom-poptsov/matrix-php/matrix/Matrix.php');

use \matrix\Matrix;

$m = new Matrix('https://example.org:8448', 'secret-token');

// 'true' means that the created user will be granted admin rights.
try {
    $m->request_registration('alice', 'passw0rd', true);
} catch (\matrix\Matrix_exception $e) {
    if ($e->get_errcode() == 'M_USER_IN_USE') {
        // Handle specific error.
    }
    // Do something else.
}

```

### Change your password, if you wish
```
$result = $session->change_password('passw0rd', 'passw1rd');
```

### Login to the server
```
require_once('vendor/artyom-poptsov/matrix-php/matrix/Matrix.php');

use \matrix\Matrix;

$m = new Matrix('https://example.org:8448', 'secret-token');

$auth_methods = $m->get_available_login_methods();

// Only 'm.login.password' currently supported.
$session = $m->login('m.login.password', 'alice, 'passw0rd');
```

### Create a room (using existing session)
```
$room = false;
try {
    $room = $session->create_room("test");
} catch (Matrix_exception $e) {
    if ($e->get_errcode() == "M_ROOM_IN_USE") {
        // Handle specific error.
    }
    // Do something else.
}
```

### Send a message to a room
```
$session = $m->login('m.login.password', 'alice, 'passw0rd');
$room = new Room('#test:example.org:8448, '!room-id:example.org:8448');
$event_id = $session->send_message($room, 'm.text', "hello");
```

### Get admin rights
```
require_once('vendor/artyom-poptsov/matrix-php/matrix/Matrix.php');

use \matrix\Matrix;

$m       = new Matrix('https://example.org:8448', 'secret-token');
$session = $m->login('m.login.password', 'alice, 'passw0rd');

// This method call may fail if 'alice' has insufficient rights
//   ("This incident will be reported", you know):
$admin_session = $session->sudo();
```

### Do your admin stuff
```
// 'make_fqn' method produces a Fully Qualified Name for the current Matrix server.
// For example: '@alice:example.org':
$fqn = $matrix->make_fqn('alice');

// 'false' here means that the user for whom the password was reset should NOT be
// logged out from all their devices (this is 'true' by default):
$admin_session->reset_password($fqn, 'new_password', false);
```

### Deactivate an account
```
$fqn = $matrix->make_fqn('alice');
$admin_session->deactivate_account($fqn);
```
