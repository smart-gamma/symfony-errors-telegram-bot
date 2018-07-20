# Smart Gamma Telegram bot bundle for Symfony errors project's chat notifications

> The Telegram bot allow to send notifications about Symfony critical errors to the configured project chat   

## Install

````
composer require smart-gamma/symfony-errors-telegram-bot
````

## Configuration 

#### Symfony version < 4.0

Add to AppKernel.php
 
 ````
  new Gamma\ErrorsBundle\GammaErrorsBundle(),
 ````
 
 #### Symfony with dotenv
 
 add to app/config/service.yaml
 
 ````
 parameters:
    gamma.base_host: '%env(BASE_URI)%'
 ````
 
 Define "BASE_URI" at .env or replace it with your env variable that defines base uri of your project.
 
  #### Symfony without dotenv
  
add to parameter.yml

````
    gamma.base_host: 'put your base uri here'
````
