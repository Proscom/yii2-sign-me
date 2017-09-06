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

```

Once the extension is installed, simply use it in your code by  :

```php
$sign = Yii::$app->signMe;
    
$sign->userPhone = '+71234567890';
    
$file = new \proscom\signMe\File($filename, $content);
    
$signResult = $sign->sign($file);
    
$checkResult = $sign->check($file);
```
OR
```php
$sign = new \proscom\signMe\SignMe([
    'apiKey' => 'SIGNME_API_KEY',
    'userPhone' => '+71234567890',
    'pathToCertificate' => 'path/to/certificate'
]);
    
$file = new \proscom\signMe\File($filename, $content);
    
$signResult = $sign->sign($file);
    
$checkResult = $sign->check($file);
```
