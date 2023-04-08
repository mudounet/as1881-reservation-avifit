
<?php

// hack version example that works on both *nix and windows
// Smarty is assumend to be in 'includes/' dir under current script
define('SMARTY_DIR',str_replace("\\","/",getcwd()).'/smarty-4.3.0/libs/');

define('CFG_DIR_ROOT',str_replace("\\","/",getcwd()).'/smarty/');

require_once(SMARTY_DIR . 'Smarty.class.php');

// The setup.php file is a good place to load
// required application library files, and you
// can do that right here. An example:
// require('guestbook/guestbook.lib.php');

class Smarty_Aviron extends Smarty {

   function __construct()
   {
        // Class Constructor.
        // These automatically get set with each new instance.

        parent::__construct();
		
		$this->setTemplateDir( CFG_DIR_ROOT . '/templates/' );
		$this->setCompileDir( CFG_DIR_ROOT . '/templates_c/' );
		$this->setConfigDir( CFG_DIR_ROOT . '/configs/' );
		$this->setCacheDir( CFG_DIR_ROOT . '/cache/' );

        //$this->setTemplateDir('/web/www.example.com/guestbook/templates/');
        //$this->setCompileDir('/web/www.example.com/guestbook/templates_c/');
        //$this->setConfigDir('/web/www.example.com/guestbook/configs/');
        //$this->setCacheDir('/web/www.example.com/guestbook/cache/');

        //$this->caching = Smarty::CACHING_LIFETIME_CURRENT;
		$this->caching = 0;
		$this->debugging = false;
		
		#define( 'CFG_DIR_TEMPLATES', $this->getTemplateDir(0) );
   }

}
?>