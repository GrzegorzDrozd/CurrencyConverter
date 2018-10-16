# Example library

This code contains only business logic for currency exchange rate calculations. To see UI please use Example application from this repository: https://github.com/GrzegorzDrozd/ExampleApplication 

## Installation

To install as a part of different project please add the following repository to your composer.json file:

```
    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:GrzegorzDrozd/CurrencyConverter.git"
        }
    ]

```

Execute following command: 
``` 
composer require grzegorzdrozd/currency-converter
```


## Requirements
This library was written on php 7.2.10. I did not test it on different version of php.
Required extensions: curl, openssl, sockets
