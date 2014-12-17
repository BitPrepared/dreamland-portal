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

