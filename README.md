# Advanced Integration Sample Application - HTML/PHP

This sample app demonstrates how to integrate with ACDC using PayPal's REST APIs.

## Before You Code

1. **Setup a PayPal Account**

   To get started, you'll need a developer, personal, or business account.

   [Sign Up](https://www.paypal.com/signin/client?flow=provisionUser) or [Log In](https://www.paypal.com/signin?returnUri=https%253A%252F%252Fdeveloper.paypal.com%252Fdashboard&intent=developer)

   You'll then need to visit the [Developer Dashboard](https://developer.paypal.com/dashboard/) to obtain credentials and to make sandbox accounts.

2. **Create an Application**

   Once you've setup a PayPal account, you'll need to obtain a **Client ID** and **Secret**. [Create a sandbox application](https://developer.paypal.com/dashboard/applications/sandbox/create).



## How to Run Locally
1. Replace your Client ID & Client Secret in the server/public/.env file:
2. Open the `.env` file in a text editor and replace the placeholders with the appropriate values.
3. Follow the below instructions to setup & run server.


## Install the Composer

Run this command to install `composer` in terminal.

```bash
brew install composer
```


## To install the dependencies

```bash
composer install
```


## To run the application in development, you can run these commands 

```bash
composer start
```

After that, open `http://localhost:8888` in your browser.

That's it!
