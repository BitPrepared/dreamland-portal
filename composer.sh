#!/bin/bash

rm composer.lock

if [ "$TRAVIS_PHP_VERSION" = "5.3.29"  ]; then
    echo "5.3 detected"
    cp composer.5.3.json composer.json
fi

composer install --dev -n --prefer-source




