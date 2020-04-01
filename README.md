# Matrix PHP
A simple library that allows to interact with a Matrix instance through the API.

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

## Examples
### Account creation
```
include 'Matrix.php';
$m = new \matrix\Matrix('https://homeserver:8448', 'secret-token');

// 'true' means that the created user will be granted admin rights.
try {
    $m->request_registration('alice', 'passw0rd', true);
} catch (\matrix\Matrix_exception $e) {
    if ($e->get_errcode() == 'M_USER_IN_USE') {
        print($e->get_error() . '\n');
    }
}

```

### Login
```
include 'Matrix.php';
$m = new \matrix\Matrix('https://homeserver:8448', 'secret-token');

$auth_methods = $m->get_available_login_methods();

$session = $m->login('m.login.password', 'alice, 'passw0rd');
```

### Create a room
```
$session = $m->login('m.login.password', 'alice, 'passw0rd');

$room = false;
try {
    $room = $session->create_room("test");
} catch (\matrix\Matrix_exception $e) {
    if ($e->get_errcode() == "M_ROOM_IN_USE") {
        print($e->get_error() . '\n');
    }
}
```

### Send a message to a room
```
$session = $m->login('m.login.password', 'alice, 'passw0rd');
$room = new \matrix\Room('#test:homeserver:8448, '!room-id:homeserver:8448');
$event_id = $session->send_message($room, 'm.text', "hello");
```

## License
GNU General Public Licence v3.0 or later.

