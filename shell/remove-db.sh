#!/bin/bash
docker volume rm `basename $PWD`_db_app
rm -f symfony/.db-created
rm -f mysql-variables.env
rm -f symfony-variables.env
