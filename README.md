# Cache Purger - v 0.0.6

## Introduction
Cache Purger is a small (and simple?) commandline tool to run flush / purge of data based on different cache technologies.
You can run this tool during your deployment to clear your caches in an easy way. 

For example, if you use Akamai and Varnish to cache your site, both caches have to be cleared after a deployment. Both technologies are supported by this tool.

Each command you can run is a wrapper for the custom clear command of each technology. Below you can find the commands that are executed in the background.

- Akamai: We call the CCU v3 API. For this technology no shell command exists.
- Redis: wrapped commands are: 
    - ```redis-cli FLUSHALL ```
    - ```redis-cli FLUSHDB ```
    - ```redis-cli DEL ```
- Varnish: wrapped CURL command like this:
    - ```curl -I -XBAN <VARNISH_HOST> -H 'X-Ban-Url: ^<ROUTE>' -H 'X-Ban-Host: <SITE_HOST>'```

## Requirements

You have to run Cache Purger in a PHP 7.x environment.

## Download

The easiest way to use Cache Purger is download the phar file and run your commands.

[Download Page](https://github.com/werfux/cachepurger/wiki/Cache-Purger-Phar-Download)  


## Setup
When you run Akamai purge commands for the first time the tool will ask you for your credentials. When all credentials a given, this tool will create an "
.edgerc" file with the credentials in the same folder where Cache Purger is saved.

## Configuration
Cache Purger requires/expects a configuration file, named purge.yml, the same directory. (You can specify a different location.)
This file must be written in [YAML notation](http://lzone.de/cheat-sheet/YAML). 

Currently the following properties are supported:

- domain **(required)**: This string value defines the domain you want to purge with Akamai or Varnish command.
- routes **(required/optional)**: This is a list/array of routes you want to purge. For the Akamai command, this is an required setting. Other commands 
supporting a full purge.
- varnish_hosts **(optinal)**: A list of Varnish hosts where you want to trigger the purge request. You can ignore this setting if you don't want to handle any 
Varnish stuff.
- redis_connections **(optinal)**: A list of settings to connect with Redis servers. You can ignore this setting if you don't want to handle any 
Redis stuff. The connections are Objects with the following properties:
    - host **(required)**
    - port **(optinal| default: 6379)**
    - password **(optional)**
    
Here is a full example of a purge.yml:

```yml
domain: example.org

routes:
  - "/foo/"
  - "/bar/"

varnish_hosts:
  - varnish01.example.org
  - varnish02.example.org

redis_connections:
  -
    host: redis01.example.org
    port: 6379
    password: 'super_secret'
  -
    host: redis02.example.org
    port: 6379
    password: 'super_secret'
```    

## Usage

To get an overview about available commands an options run 

```php pruger.phar help```

### Run Akamai Purge:

- ```php purger.phar akamai:purge --domain=example.org```
- ```php purger.phar akamai:purge --domain=example.org --file=/path/my_purge_file.yml```

### Run Varnish Purge:

- ```php purger.phar varnish:purge --domain=example.org```
- ```php purger.phar varnish:purge --domain=example.org --file=/path/my_purge_file.yml```
- ```php purger.phar varnish:purge --force-all --domain=example.org --file=/path/my_purge_file.yml```

### Run Redis Purge:

- ```php purger.phar redis:purge --file=/path/my_purge_file.yml```
- ```php purger.phar redis:purge --pattern=<REGEX_PATTERN to flush only specific keys> --file=/path/my_purge_file.yml```
- ```php purger.phar redis:purge --force-all --file=/path/my_purge_file.yml```
