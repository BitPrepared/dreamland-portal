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