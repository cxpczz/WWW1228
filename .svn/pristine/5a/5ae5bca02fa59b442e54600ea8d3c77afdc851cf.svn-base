<?php
// include ('log4php/Logger.php');
// Logger::configure('log4php.xml');//XML配置
$basedir = dirname(__FILE__);

include ($basedir . '\phpcms\log4php\Logger.php');
echo $basedir . '\log4php.xml';
Logger::configure($basedir . '\log4php.xml');
 // XML配置
class Foo
{

    /**
     * Holds the Logger.
     */
    private $log;

    /**
     * Logger is instantiated in the constructor.
     */
    public function __construct()
    {
        // The __CLASS__ constant holds the class name, in our case "Foo".
        // Therefore this creates a logger named "Foo" (which we configured in the config file)
        $this->log = Logger::getLogger(__CLASS__);
        // var_dump($this->log);
    }

    /**
     * Logger can be used from any member method.
     */
    public function go()
    {
        /* 同业执行脚本中，不能出现两次相同函数 */
        $this->log->info("My third message.1111<br>\r\n"); // Not logged because INFO < WARN
        $this->log->info('时间是：' . date("Y-m-d H:i:s"));
    }
}

$foo = new Foo();
$foo->go();
/*END面向对象*/  