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
        chat_id: '%env(GAMMA_ERRORS_TELEGRAM_CHAT_ID)%'
````

## 3. Creating a bot

Start a conversation with @BotFather.

````
You: /newbot
>>>>>>>>>>
@BotFather: Alright, a new bot. How are we going to call it? Please choose a name for your bot.
<<<<<<<<<<
You: Sample Error Bot
>>>>>>>>>>
@BotFather: Good. Now let's choose a username for your bot. It must end in `bot`. Like this, for example: 
TetrisBot or tetris_bot.
<<<<<<<<<<
Me: test_error_bot
>>>>>>>>>>
@BotFather: Done! Congratulations on your new bot. You will find it at telegram.me/cronus_bot. You can now add a 
description, about section and profile picture for your bot, see /help for a list of commands. By the way, when 
you've finished creating your cool bot, ping our Bot Support if you want a better username for it. Just make sure 
the bot is fully operational before you do this.

Use this token to access the HTTP API:
111111:xxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxx

For a description of the Bot API, see this page: https://core.telegram.org/bots/api
````

## 4. Get chat identifier

After the created bot was added to your project's group you should retrieve its chat_id 

Make POST request to URL: https://api.telegram.org/bot_token_/getUpdates

Example:

Request

````
curl -X POST https://api.telegram.org/bot111111:xxxx-xxxxxxxxxxxxxxxxxxxxxxxxxxxxx/getUpdates
````

Response

````
{
"ok": true,
"result": [
  {
    "update_id": 222222,
    "message": {
      "message_id": 3333,
      "from": {
        "id": 444444,
        "first_name": "Test",
        "last_name": "Test",
        "username": "test"
      },
      "chat": {
        "id": -111111
        "first_name": "Test",
        "last_name": "Test",
        "username": "test",
        "type": "private"
      },
      "date": 1480701504,
      "text": "test"
    }
  }
]
}
````

chat_id is the number "-111111".

5. Slack support

In order to have additional channel of notification you can enable Slack with webhook integration

- add "Incoming WebHooks" app to your Slack and copy webhook url from the settings

- configure 

````
gamma_errors:
    enabled: true
    telegram_channel:
    ....  
    slack_channel:
        webhook:  '%env(GAMMA_ERRORS_SLACK_WEBHOOK)%' 
        channel:  '%env(GAMMA_ERRORS_SLACK_CHANNEL)%' 
````