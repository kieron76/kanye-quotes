# Kanye Quotes API

Welcome to this project, it's a simple API to return 5 Kanye quotes.

# Requirements

Tested on

PHP: 8.3.8

Mysql: 8

Composer: 2.7.7

Redis 

git

# Download

Please clone the repo, this can be done on the command line. 

```bash
git clone git@github.com:kieron76/kanye-quotes.git
```

# Docker

One can use sail to spin up the development environment. Most of sail is stored in the vendor folder which is not part of version control.

You can install the dependencies in a reasonably hacky way by doing the below.

```bash
cd kanye-quotes
cp .env.example .env
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs && php artisan key:generate
```

This creates a temporary container to install the required dependencies. The docker run statement is taken straight from the laravel docs. The u flag maps the user on the host machine to the sail user in the container. This allows the correct permissions in the box for the correct working of the site and the ability to develop on the same files on the host machine. Once run, you can then run:

```bash
./vendor/bin/sail up
```


# Alternative Method

If you are unable to use docker, you can create an environment with the above requirements and use for example apache or nginx to run the web server. Unfortunately, example configuration for these has not been provided.

Make sure you copy the .env.example file to .env and configure it according to your environment.

Please also run

```
composer install
```

# Post Installation Config

Migrations need to be run. Within the environment:

```
php artisan migrate
```

You will need to make sure that these settings are correct and at least an API_KEY provided in the .env file. The application defaults to server maintenance mode return 503 for all endpoints until the configuration is acceptable.

```
API_TOKEN=
KANYE_API=https://api.kanye.rest/
```

# Using the API

You can use a variety of API tools to test the application e.g. postman. Make sure that you provide the value that you supply in the API_TOKEN environment variable as a header named 'api-token'.

If you manage to spin this up locally, you could use curl to quickly test the root route:

```curl
curl --location 'localhost/' \
--header 'api-token: MYRANDOMSTRING' 
```

This should return 5 Kanye quotes. If you execute this again, you should receive the same 5 quotes from redis cache.

## Refresh

It is not until you refresh that you get another 5 quotes. This can be done with the endpoint

`/refresh`

And if you need more, you can supply a page number to this endpoint to get the next 5, for example:

`/refresh/3`

# Testing

There are some tests in place to make sure this works. These can be executed by running this within the environment:

`php artisan test`