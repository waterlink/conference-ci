#!/bin/bash

php_ci="/opt/php/bin/php -c /opt/php/etc index.php"

scheme=$1

cd www
$php_ci test $scheme
