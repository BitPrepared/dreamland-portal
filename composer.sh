#!/bin/bash

rm composer.lock

echo "current php version $TRAVIS_PHP_VERSION"

if [ "$TRAVIS_PHP_VERSION" = "5.3.29"  ]; then
    echo "5.3 detected"
    cp composer.5.3.json composer.json
fi

composer install -n --prefer-source




