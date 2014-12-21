#/bin/bash

echo 'phpunit'
phpunit

echo 'phpunit di integrazione con wordpress'
phpunit --group remoteTasks

