### How to install this package?
`composer require kield-01/smsc-ua-sms-sender dev-master` to use current version

### What You need to have to use this package?

-   CakePHP 3.3.x

After You require this package, it will install `fabpot/goutte` package

### How to set up this component?
There is 2 options, how to initialize

##### Option 1
Set up credentials key in `config/app.php`:

```
    'smsc_ua' => [
        'login' => '__LOGIN__',
        'psw' => '__PASSWORD__'
    ]
```

##### Option 2
Set up credentials after loading component:

```
    use Cake\Controller\Component\SmscUaComponent;

    /**
    * Class X
    *
    * @property SmscUaComponent $SmscUa
    **/
    class X extends AppController
    {
    
        public function initialize(){
               $this->loadComponent('SmscUa');
               
               $this->SmscUa
                   ->setLogin('__LOGIN__')
                   ->setPassword('__PASSWORD__');
                   
                /** OR **/
                
                $this->SmscUa
                    ->setArgument('login', '__LOGIN__')
                    ->setArgument('psw', '__PASSWORD__');
        }
        
    }
```

### API


```
/** Allows You to add one number to current numbers list **/
$this->SmscUa->setNumbers('380666666666');

/** Allows You to add bulk to current numbers list **/
$this->SmscUa->setNumbers(['380666666666', '380666666667']);

/** Allows You to set message Text **/
$this->SmscUa->setMessageBody('Random text');

/** Allows You to change Response format **/
$this->SmscUa->setResponseFormat('string'); // Response as string data
$this->SmscUa->setResponseFormat('digits'); // Response as digits, separated by ','
$this->SmscUa->setResponseFormat('xml');    // Response as XML Document
$this->SmscUa->setResponseFormat('json');   // Response as JSON Object

/** Sending methods **/
$this->SmscUa->sendPlainTextSMS(); // Sending simple SMS
``` 
