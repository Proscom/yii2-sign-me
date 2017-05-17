proscom/yii2-sign-me
==========================
Extension for sign.me service

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist proscom/yii2-sign-me "*"
```

or add

```
"proscom/yii2-sign-me": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Config application :

```php
'components' => [
    //  ...
    'signMe' => [
            'class' => SignMe::className(),
            'apiKey' => 'SIGNME_API_KEY',
            'pathToCertificate' => 'path/to/certificate',
        ],
]
    
$sign = Yii::$app->signMe;
    
$sign->userPhone = '+71234567890';
    
$signResult = $sign->sign($filename);
    
$checkResult = $sign->check($filename);

```

Once the extension is installed, simply use it in your code by  :

```php
$sign = new SignMe([
    'apiKey' => 'SIGNME_API_KEY',
    'userPhone' => '+71234567890',
    'pathToCertificate' => 'path/to/certificate'
]);
    
$signResult = $sign->sign($filename);
    
$checkResult = $sign->check($filename);
```
