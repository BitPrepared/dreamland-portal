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

echo 'mess detector'
./vendor/bin/phpmd src/ html phpmd.xml --reportfile build/messdetector.html --exclude Tests/
./vendor/bin/phpmd src/ xml phpmd.xml --reportfile build/messdetector.xml --exclude Tests/

./vendor/bin/phpcs --report=xml --report-file=build/cover.xml src
./vendor/bin/phpcs --report=checkstyle --report-file=build/logs/checkstyle.xml --standard=build/config/phpcs.xml --ignore=*.html.php,*.config.php,*.twig.php src

./vendor/bin/phploc --log-csv build/phploc.csv --progress --git-repository . src

`ps axu | grep mailcatcher | grep ruby | awk '{print $2}' | xargs kill`

screen -X mailcatcher