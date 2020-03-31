# Matrix PHP
A simple library that allows to interact with a Matrix instance through the API.

## Dependencies
- curl

## Examples
### Account creation
```
include 'matrix.php';
$m = new \matrix\Matrix("https://homeserver:8448", "secret-token");

// 'true' means that the created user will be granted admin rights.
$m->request_registration("alice", "passw0rd", true);

```

### Login
```
include 'matrix.php';
$m = new \matrix\Matrix("https://homeserver:8448", "secret-token");

$auth_methods = $m->get_available_login_methods();

$session = $m->login('m.login.password', 'alice, 'passw0rd);
```

## License
GNU General Public Licence v3.0 or later.

