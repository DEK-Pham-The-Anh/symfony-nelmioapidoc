# Symfony as an API platform

Symfony 5 app serving as an API platform built with NelmioApiDocBundle.


## 1. Getting started

- Install packages 
```
composer install
```
- Start the server at http://localhost:8888/ 
```
./start_server.sh
```
- Interactive API documentation (Swagger UI) available at http://localhost:8888/api/doc


## 2. Middleware

Use [Event Subscribers](https://symfony.com/doc/current/event_dispatcher/before_after_filters.html) for setting up before/after filters.


### 2.1 API Authentication

Authentication should be implemented in the middleware. Use JWT tokens sent as a `Bearer` in the header or hidden inside a cookie. 


### 2.2 API protection against CSFR attacks

In the middleware check for the `referrer` of the request whether it matches allowed URL addresses specified in the `CORS_ALLOW_ORIGIN` property located either in `/public/.htaccess`' or in `/config/packages/nelmio_cors.yaml`.


### 2.3 Exceptions

Handle exceptions using [Event Listeners](https://symfony.com/doc/current/event_dispatcher.html#listeners-or-subscribers).


## 3. CORS

Enable `allow_credentials` if you are sending JWT tokens in the header or as a cookie. 

`/config/packages/nelmio_cors.yaml`:

```
nelmio_cors:
    defaults:
        origin_regex: true
        allow_credentials: true
        # allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_origin: ['^https://(domain1.com|domain2.com)$']
        allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
    paths:
        '^/': null
```


## 4. Apache

After installation `symfony/apache-pack` package creates a `.htaccess` file inside your `/public` folder. 

`.htaccess` should always contain these lines:

```
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule !\.(pdf|js|ico|gif|jpg|png|css|rar|zip|tar|svg\.gz)$ index.php [L]
```


## 5. NelmioApiDocBundle


### 5.1 Documentaion Export

Expose your documentation as both JSON and YAML swagger compliant -  `/config/packages/nelmio_api_doc.yaml`:

```
app.swagger_json:
    path: /api/doc.json
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger_json }
app.yaml_yaml:
    path: /api/doc.yaml
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger_yaml }
```


### 5.2 Annotation

Latest NelmioApiDocBundle (v4.4) uses [Swagger-PHP Annotation](http://zircote.github.io/swagger-php) which is based on [OpenApi v^3.0 specs](https://swagger.io/specification/).


## 6. Problems


### 6.1 Disable cache

Caching can be disabled in `/config/services.yaml`:

```
framework:
    cache:
        app: cache.adapter.null
        system: cache.adapter.null
services:
    cache.adapter.null:
        class: Symfony\Component\Cache\Adapter\NullAdapter
        arguments: [~] # a trick to avoid arguments errors on compile-time
```


### 6.2 DEV/localhost is working fine, but PROD says 500 Server Error

Probably a cache problem. Empty the `/var/cache` folder.