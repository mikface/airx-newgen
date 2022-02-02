# Symfony 6 + PHP 8.1 + MySQL 8 + NGINX boilerplate

Easy kick off for Symfony REST API / microservice running in Docker container using LEMP stack. Pre-packed with basic code quality tools. 

## Prerequisites
1) Docker Engine (https://docs.docker.com/engine/install/)
2) Docker Compose (https://docs.docker.com/compose/install/)
## Installation and Configuration

1) `git clone git@github.com:mikface/symfony-docker.git <your-folder-name>` 

    or

    `git clone https://github.com/mikface/symfony-docker.git <your-folder-name>`

2) `cd <your-folder-name>`

3) `make`

Then access [symfony.localhost:8081](http://symfony.localhost:8081), you should see the Symfony welcome page.

(**url** / **port** can be changed in **docker-compose.yml::32** / **docker-compose.yml::36**)

### Stop docker:

`make down`

### Remove database:

`make clean-database`

### Add user for app:

`make add-user`

### List all app users:

`make list-users`

---

## Authentication

To authorize, first add user. Then send POST request to obtain the JWT token:

`curl -X POST -H "Content-Type: application/json" http://symfony.localhost:8081/login_check -d '{"email":"user@example.com","password":"test"}'`

You should now be able to visit page for logged users:

`curl --location --request GET 'http://symfony.localhost:8081/auth/hello-world/greet' --header 'Authorization: Bearer {token}'`

---

## Code quality

### Run Codesniffer:

`make cs`

### Run cs autofix:

`make fix`

### Run phpstan:

`make phpstan`

### Reinit git repo:

`make init-git`
