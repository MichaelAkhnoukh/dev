<?php

/**
 * @author MichaelAkhnoukh
 * https://github.com/MichaelAkhnoukh
 * 
 * Description:
 * This php function autoloads classes as you instantiate the object so you don't
 * have to include their files anymore. All you have to do is to require this 
 * file at the top of your php script document
 * 
 * Usage:
 * 1- Include all the directories that might conatin your classes
 *    in the $directories array
 * 2- Add classes's files extensions int the $extensions array if you use 
 *    extensions other than the predefined 
 *    (Good practice is name the class name and the file name always the same)
 * 3- if you are instantiating from a class within a namespace you should use the 
 *    fully qualified name. Ex: $foo = Main\utils\ExampleModel
 * 4- using with classes from global namespace (without namespaces) 
 *    instantiating is like traditional. Ex: $foo = ExampleModel
 */
function autoload($className) {
    $directories = array("Models/", "Controllers/");
    $extensions = array('%s.php', '%s.php.inc', '%s.class.php', 'class.%s.php');

    foreach ($directories as $dir) {
        foreach ($extensions as $format) {
            $parts = explode('\\', $className);
            $path = $dir . sprintf($format, end($parts));
            if (file_exists($path)) {
                require_once $path;
                return TRUE;
            }
        }
    }
}

spl_autoload_register('autoload');
?>
