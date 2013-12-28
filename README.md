#OAuthSession

Helps you to bind [Aura.Session](https://github.com/auraphp/Aura.Session) with 
[Lusitanian OAuth](https://github.com/Lusitanian/PHPoAuthLib)

## Foreword

### Requirements

This library requires PHP 5.3 or later.

* [Aura.Session](https://github.com/auraphp/Aura.Session)
* [Lusitanian OAuth](https://github.com/Lusitanian/PHPoAuthLib)

### Installation

This library is installable and autoloadable via Composer with the following
`require` element in your `composer.json` file:

    "require": {
        "friendsofaura/oauthsession": "dev-master"
    }

### Tests

[![Build Status](https://travis-ci.org/friendsofaura/OAuthSession.png?branch=dev-master)](https://travis-ci.org/friendsofaura/OAuthSession)

To run the tests at the command line, go to the _tests_ directory and issue `phpunit`.

[phpunit]: http://phpunit.de/manual/

### PSR Compliance

This library attempts to comply with [PSR-1][], [PSR-2][], and [PSR-4][]. If
you notice compliance oversights, please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md


### Example

```php
<?php
/**
 * Example of retrieving an authentication token of the Github service
 *
 * PHP version 5.4
 *
 * @author David Desberg <david@daviddesberg.com>
 * @author Pieter Hordijk <info@pieterhordijk.com>
 * @author Hari KT <kthari85@gmail.com>
 * @copyright Copyright (c) 2012 The authors
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 */

require __DIR__ . '/vendor/autoload.php';

use OAuth\OAuth2\Service\GitHub;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use FOA\OAuthSession\AuraSession;
use Aura\Session\Manager;
use Aura\Session\SegmentFactory;
use Aura\Session\CsrfTokenFactory;
use Aura\Session\Randval;
use Aura\Session\Phpfunc as AuraSessionPhpfunc;

$servicesCredentials['github']['key'] = ''; 
$servicesCredentials['github']['secret'] = '';

$session_manager = new Manager(
    new SegmentFactory,
    new CsrfTokenFactory(
        new Randval(
            new AuraSessionPhpfunc
        )
    ),
    $_COOKIE
);
$uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
$currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
$serviceFactory = new \OAuth\ServiceFactory();
        
// Session storage
$storage = new AuraSession($session_manager);

// Setup the credentials for the requests
$credentials = new Credentials(
    $servicesCredentials['github']['key'],
    $servicesCredentials['github']['secret'],
    $currentUri->getAbsoluteUri()
);

// Instantiate the GitHub service using the credentials, http client and storage mechanism for the token
/** @var $gitHub GitHub */
$gitHub = $serviceFactory->createService('GitHub', $credentials, $storage, array('user'));

if (!empty($_GET['code'])) {
    // This was a callback request from github, get the token
    $gitHub->requestAccessToken($_GET['code']);

    $result = json_decode($gitHub->request('user/emails'), true);

    echo 'The first email on your github account is ' . $result[0];

} elseif (!empty($_GET['go']) && $_GET['go'] === 'go') {
    $url = $gitHub->getAuthorizationUri();
    header('Location: ' . $url);

} else {
    $url = $currentUri->getRelativeUri() . '?go=go';
    echo "<a href='$url'>Login with Github!</a>";
}

```
