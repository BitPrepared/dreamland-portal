#!/bin/sh

count=`netstat -an | grep 1025 | wc -l`

if [ $count -lt 1 ];
then
    screen -d -m -S mailcatcher mailcatcher --ip 127.0.0.1 -f
fi

echo 'phpunit'
phpunit

echo 'phpunit di integrazione con wordpress'
phpunit --group remoteTasks

`ps axu | grep mailcatcher | grep ruby | awk '{print $2}' | xargs kill`

screen -X mailcatcher