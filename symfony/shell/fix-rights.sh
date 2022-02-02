#!/bin/bash

id -u "$1" >/dev/null 2>&1 || useradd "$1"

chown -R "$1:www-data" ./* ./.*