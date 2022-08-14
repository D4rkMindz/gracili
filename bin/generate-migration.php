#!/bin/php
<?php

$name = readline('Enter a new migration name: ');

system("cd config && ../vendor/bin/phinx-migrations generate --name {$name} --overwrite");
