# Smart Gamma Telegram bot bundle for Symfony errors project's chat notifications

> The Telegram bot allow to send notifications about Symfony critical errors to the configured project chat   

## 1. Install

````
composer require smart-gamma/symfony-errors-telegram-bot
````

## 2. Configuration 

### 2.1. Activate the bundle 
 
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

### 2.2. Add configuration

Put the file gamma_errors_bot.yaml to vendor/packages folder at app/config 

````
gamma_errors:
    enabled: true
    telegram_channel:
        auth_key: '%env(GAMMA_ERRORS_TELEGRAM_AUTH_KEY)%'
        chat_id: "%env(GAMMA_ERRORS_TELEGRAM_CHAT_ID)%"
````