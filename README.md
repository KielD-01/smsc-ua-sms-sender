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