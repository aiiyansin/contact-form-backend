Contact Form Backend
===================
> Simple contact form smtp backend

```html
<form method="POST" action="//example.com" accept-charset="UTF-8">
  <div><label for="name">Name:</label><input name="name" title="Your name" type="text"/></div>
  <div><label for="email">Email:</label><input name="email" title="Your e-mail" type="text"/></div>
  <div><label for="message">Message:</label><textarea name="message" rows="5"></textarea></div>
  <div><button type="submit">Submit</button></div>
</form>
```

Once configured, it will send the contact form submission to the configured email. A reply to address will automatically be added as the `email` field.

## Requirements

* PHP >= 5.4
* Composer

## Installation

Clone repo, point webroot to the `public` directory and run `composer install`.

## Configuration

Copy the example config file `config.php.example` to `config.php` and fill in the required fields (`server`, `port`, `username` and `password`). All other fields are optional as they have some predefined defaults. The `to` field needs to be set in either the config or in the form post.
