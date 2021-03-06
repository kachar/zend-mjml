## ZF2 Module for MJML to HTML rendering

This module is based on: MJML
The only open-source framework that makes responsive-email easy https://mjml.io

<p align="center">
  <a href="https://github.com/mjmlio/mjml" target="_blank">
    <img width="250"src="https://cloud.githubusercontent.com/assets/6558790/12672296/7b66d8cc-c675-11e5-805d-c6d196320537.png">
  </a>
</p>

With this module you can define your email templates as MJML files and render them using Zend `ViewModel`.
The MJML markup will be sent to https://mjml.io/try-it-live in order to be transformed to HTML.

The package supports:
- simple variable replacement after the markup has been converted. (`*.mjml` files)
- complex templating system using PHP inside the mjml markup. (`*.pmjml` files)

You can send the complete emails using a pre-defined email transport method.

## Requirements

* PHP Version >= 5.4
* [Zend Framework 2](https://github.com/zendframework/zf2) (2.*)
* [Guzzle Http Client](https://github.com/guzzle/guzzle) (~5)
* [Guzzle Cache Subscriber](https://github.com/guzzle/cache-subscriber) (*)

## Installation

 1. Add `"kachar/zend-mjml": "dev-master"` to your `composer.json` file and run `php composer.phar update`.

    ```json
    "require": {
        "kachar/zend-mjml": "dev-master"
    }
    ```
    ```bash
    $ php composer.phar update
    ```

 2. Add `ZendMjml` to your `config/application.config.php` file under the `modules` key.

    ```php
    'modules' => [
        'ZendMjml',
    ],
    ```

## Configuration

Under the key `mjml` you can set the following options in the configuration:

`mjmlServiceUrl`:
Sets the MJML endpoint (default `https://mjml.io`)

`timeout`:
Float describing the timeout of the request in seconds. Use 0 to wait indefinitely (default value `10`).

`connectTimeout`:
Float describing the number of seconds to wait while trying to connect to a server. Use 0 to wait indefinitely (default value `1.5`).

`transportAdapter`:
The adapter that will be used to send the actual email. Two possible configurations:
* as a string - Any service that implements `Zend\Mail\Transport\TransportInterface` and can be fetched from ServiceLocator.
```php
    'mjml' => [
        'transportAdapter' => [
            'type' => 'sendmail',
            'options' => [
                // see http://www.sendmail.org/~ca/email/man/sendmail.html
            ],
        ],
    ],
```
* as factory array - Configuration options based on `\Zend\Mail\Transport\Factory`. For advanced factory options you can see (official documentation)[http://framework.zend.com/manual/current/en/modules/zend.mail.transport.html#zend-mail-transport].
```php
    'mjml' => [
        'transportAdapter' => 'Zend\Mail\Transport\Sendmail',
    ],
    'service_manager' => [
        'invokables' => [
            'Zend\Mail\Transport\Sendmail' => 'Zend\Mail\Transport\Sendmail',
        ],
    ],
```

## Usage

### Inline MJML makrup
```php

    $service = $this->getServiceLocator()->get('Service\Mjml');
    $mjml = '
    <mj-body>
      <mj-section>
        <mj-column>
          <mj-image width="100" src="https://mjml.io/assets/img/logo-small.png"></mj-image>
          <mj-divider border-color="#F45E43"></mj-divider>
          <mj-text font-size="20px" color="#F45E43" font-family="helvetica">Hello {{ name }}</mj-text>
        </mj-column>
      </mj-section>
    </mj-body>
    ';

    // This is the final html to be sent to the recipients
    $body = $service->renderMjml($mjml);

    // You can replace simple variables inside the final output
    $body = $service->renderMjml($mjml, [
        'name' => 'direct example',
    ]);

    // Sending the email
    $email = $service->composeEmail($body);
    $email->setTo('email@example.com');
    $email->setSubject('Sample responsive MJML email');
    $service->sendEmail($email);
    // or
    echo $body;
```

### Using ViewModel that loads MJML makrup file
```php

    $service = $this->getServiceLocator()->get('Service\Mjml');

    $view = new ViewModel();
    $view->name = 'John Doe';
    $view->setTemplate('mjml/plain.mjml');
    // This is the final html to be sent to the recipients
    $body = $service->renderView($view);

    // Sending the email
    $email = $service->composeEmail($body);
    $email->setTo('email@example.com');
    $email->setSubject('Sample responsive MJML email using ViewModel');
    $service->sendEmail($email);
```

### Using ViewModel that loads PHP-MJML makrup file
```php

    $view = new ViewModel();
    // This will be replaced using str_replace in the final template
    $view->name = 'PHP MJML Template';

    // This will be replaced using php in the initial mjml template
    $view->products = [
        [
            'name' => 'Ham',
            'price' => '1.50',
            'quantity' => 5,
        ],
        [
            'name' => 'Cheese',
            'price' => '3.75',
            'quantity' => 3,
        ],
        [
            'name' => 'Bread',
            'price' => '5.00',
            'quantity' => 10,
        ],
    ];
    $view->setTemplate('mjml/php-mjml.pmjml');
    // This is the final html to be sent to the recipients
    $body = $service->renderView($view);

    // Sending the email
    $email = $service->composeEmail($body);
    $email->setTo('email@example.com');
    $email->setSubject('Sample responsive MJML email using PhpRenderer');
    $service->sendEmail($email);
```
