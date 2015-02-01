| Tests | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- | --------- | ------------ |
| [![Build Status](https://travis-ci.org/BitPrepared/dreamland-portal.svg?branch=master)](https://travis-ci.org/BitPrepared/dreamland-portal) [![Code Climate](https://codeclimate.com/github/BitPrepared/dreamland-portal/badges/gpa.svg)](https://codeclimate.com/github/BitPrepared/dreamland-portal) [![Coverage Status](https://coveralls.io/repos/BitPrepared/dreamland-portal/badge.png)](https://coveralls.io/r/BitPrepared/dreamland-portal) | B | C | [![Dependency Status](https://www.versioneye.com/user/projects/549a92496b1b81d9a40001ad/badge.svg?style=flat)](https://www.versioneye.com/user/projects/549a92496b1b81d9a40001ad) |




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

Da provare come alternativa: https://github.com/mailhog/MailHog forse funziona meglio con travis (sample: https://gist.github.com/varghesejacob/68caf7aeee53305a1ffa#file-mailhog-bash-script)
oppure https://github.com/alexandresalome/mailcatcher alcuni stanno integrando mailcatcher con behat 


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

### Php.ini

```
php -i | grep 'Configuration File'
```

result: 

```
Configuration File (php.ini) Path => /usr/local/etc/php/5.4
Loaded Configuration File => /usr/local/etc/php/5.4/php.ini
```

nel file /usr/local/etc/php/5.4/php.ini assicurarsi che ci sia : 

```
sendmail_path = /usr/bin/env catchmail -f from@example.com
```

### Dipendenze

```
php vendor/clue/graph-composer/bin/graph-composer export . --format=png export.png
```
