<?php

class Plugin extends Bot {

    private $plugins = array();
    
    function __construct() {
        
    }

    /*
     * Loads a plugin
     * 
     * @return bool
     */

    public function load($plugin) {
        
    }

    /*
     * Loaded Plugin List
     * 
     * @return array
     */

    public function loaded() {
        foreach(get_declared_classes() as $lplug){
            $bot->raw($lplug);
        }
    }

    /*
     * Plugin Register
     * 
     * Registers a plugin
     * 
     * @return void
     */

    public function register($class) {
        $this->plugins[] = $class;
        //$class->__construct($this);
    }

    /*
     * Plugin Event
     * 
     * Checks for a function that matches the command
     * 
     * @return void
     */

    public function event($class, $command, $args) {
        $plugin = strtolower($class);
        $command = strtolower($command);
        
        //$class::$command($args['channel'], $args['user'], $args['args']);



        General::test($args['channel'], $args['user'], $args['args']);
        if(method_exists($plugin, $command)) {
            echo "Class and Function Loaded \r\n";
            call_user_func($command, $args);
        }
    }

}
