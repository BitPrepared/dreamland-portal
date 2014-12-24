| Tests | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- | --------- | ------------ |
| [![Build Status](https://travis-ci.org/BitPrepared/dreamland-portal.svg?branch=master)](https://travis-ci.org/BitPrepared/dreamland-portal) [![Code Climate](https://codeclimate.com/github/BitPrepared/dreamland-portal/badges/gpa.svg)](https://codeclimate.com/github/BitPrepared/dreamland-portal) [![Test Coverage](https://codeclimate.com/github/BitPrepared/dreamland-portal/badges/coverage.svg)](https://codeclimate.com/github/BitPrepared/dreamland-portal) [![Coverage Status](https://coveralls.io/repos/BitPrepared/dreamland-portal/badge.png)](https://coveralls.io/r/BitPrepared/dreamland-portal) | B | C | [![Dependency Status](https://www.versioneye.com/user/projects/549a92496b1b81d9a40001ad/badge.svg?style=flat)](https://www.versioneye.com/user/projects/549a92496b1b81d9a40001ad) |




Portale iscrizioni
================

### Installazione

```bash 
bower install
composer install
sh compile.sh
```

Usando la basic auth su json-rest-api, bisogna mettere nel .htaccess:

```
# basic auth
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

### Mailcatcher dependency
bundle
o
gem install mailcatcher

la parte web: http://127.0.0.1:1080/, nei test vengono cancellate automaticamente le email!


### Come lanciare i test unitari

Caso test specifico
```
phpunit --filter testStep1
```

Caso gruppo di test
```
phpunit --group remoteTasks
```

### Lunk Utili

* https://coderwall.com/p/5mtq6q/encrypt-your-code-climate-repo-token-for-public-repositories-on-travis-ci
* http://edorian.github.io/php-coding-standard-generator/#phpmd
