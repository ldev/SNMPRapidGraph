<?php
   /**
    *   Class for providing semi-persistent storage access to RAM-disk.
    *   Data is stored in key <-> value relationship.
    *   @author Jonas Lindstad <jonaslindstad@gmail.com>
    */
    
    function getTime($path){
        clearstatcache($path);
        $dateUnix = shell_exec('stat --format "%y" '.$path);
        $date = explode(".", $dateUnix);
        return filemtime($path).".".substr($date[1], 0, 8);
    }
    
    class ramdisk{
        var $identifier = false; # used for writing to ramdisk
        var $logg = array();
        var $storage_path = false;
        var $debug = false;
        var $default = array(
            'ramdisk_path' => '/run/shm',
            'application_directory' => '/graflol/',
            'chmod' => 770
        );

        
       /**
        *   Construct. Provides path information, with fallback to sane default settings for most linux OS-es.
        *   @var $ramdisk_path string Path of RAM-disk mountpoint
        *   @var $application_directory Path of application directory within RAM-disk mountpoint. If this does not exist, the function will try to create it
        */
        public function __construct($ramdisk_path = false, $application_directory = false){
            /*
                Provides fallback if no ramdisk path or application directory is specified
            */
            if($ramdisk_path !== false){
                $this->storage_path = $ramdisk_path;
            }else{
                $this->storage_path = $this->default['ramdisk_path'];
            }
            if($application_directory !== false){
                $this->storage_path .= $application_directory;
            }else{
                $this->storage_path .= $this->default['application_directory'];
            }
            
            if(is_dir($this->storage_path)){
                if(is_readable($this->storage_path) && is_writable($this->storage_path)){
                    $this->logg('Directory is readable/writable: ' . $this->storage_path);
                }else{
                    if(rmdir($this->storage_path) && mkdir($this->storage_path, octdec($this->default['chmod']))){
                        $this->logg('Deleted and created directory ' . $this->storage_path);
                        clearstatcache();
                        $this->logg($this->storage_path . ' Permissions: ' . octdec(fileperms($this->storage_path)));
                    }else{
                        $this->logg('Could not delete and create ' . $this->storage_path . '. Permissions: ' . octdec(fileperms($this->storage_path)));
                    }
                }
            }else{
                if(!mkdir($this->storage_path, octdec($this->default['chmod']))){
                    $this->logg($this->storage_path . ': not existing/creatable');
                    die('Fatal error');
                }else{
                    $this->logg($this->storage_path . ': Directory created');
                }
            }
            
            $this->logg('construct() completed');
        }


       /**
        *   Function for providing internal logging/performance testing
        *   @return none
        */
        private function logg($what){
            $this->logg[] = microtime(true) . '::' . $what;
        }


       /**
        *   Function for toggelig dumping of the ramdisk object on destruction.
        *   @var bool $bool - Wether debugging info should be printed or not
        */
        public function debug($bool){
            $this->debug = (bool) $bool;
        }
        
        
       /**
        *   Unique identificator for the next operation
        *   @var string $identifier - identifying key
        *   @return object self
        */
        public function identify($identifier){
            if($this->identifier === false){
                $this->identifier = $this->clean_identifier($identifier);
                $this->logg('Identifier set to "' . $this->identifier . '"');
            }
            return $this;
        }
        
       
       /**
        *   Gets data from previously provided identifier
        *   @return string data
        */
        public function get(){
            if($this->identifier !== false){
                if(is_readable($this->storage_path . $this->identifier)){
                    $this->logg('Returned the contents of ' . $this->storage_path . $this->identifier);
                    $return = (string) file_get_contents($this->storage_path . $this->identifier);
                    $this->identifier = false;
                    return $return;
                }else{
                    $this->logg($this->storage_path . $this->identifier . ': Not existing/readable');
                    $this->identifier = false;
                    return false;
                }
            }else{
                $this->logg('No identifier provided');
                return false;
            }
        }
        
        
       /**
        *   Assign data to previously provided identifier
        *   @var string $data Data to be saved
        *   @return bool True on success, False on error
        */
        public function set($data){
            if($this->identifier !== false){
                if(file_put_contents($this->storage_path . $this->identifier, $data, LOCK_EX)){
                    $this->logg('Saved "' . $data . '" to file ' . $this->storage_path . $this->identifier);
                    $this->identifier = false;
                    return true;
                }else{
                    $this->logg('Could not save "' . $data . '" to file ' . $this->storage_path . $this->identifier);
                    $this->identifier = false;
                    return false;
                }
            }else{
                $this->logg('No identifier provided');
                return false;
            }
        }
        
       /**
        *   Returns the age of stored data. Usefull for calculating SNMP gauges.
        *   @return mixed int Seconds since the file was modified. Returns False on failure
        */
        public function age(){
            if($this->identifier !== false){
                if($age = @getTime($this->storage_path . $this->identifier)){
                    $this->identifier = false;
                    return microtime(true) - $age;
                }else{
                    $this->identifier = false;
                    return false;
                }
            }else{
                $this->logg('No identifier provided');
                return false;
            }
        }
        /*
            Returns unix timestamp or javascript timestamp (unix timestamp * 1000)
        */
        public function timestamp($jsfriendly = false){
            if($this->identifier !== false){
                if($age = @getTime($this->storage_path . $this->identifier)){
                    $this->identifier = false;
                    if($jsfriendly = true){
                        return substr(str_replace('.', '', $age), 0, 14);
                    }else{
                        return $age;
                    } 
                }else{
                    
                    $this->identifier = false;
                    return false;
                }
            }else{
                $this->logg('No identifier provided');
                return false;
            }
        }
        
       /**
        *   http://stackoverflow.com/questions/14377818/making-strings-url-safe
        *   Replaces all except a-z0-9 with "-". Removing multiple "-", and ensure no starting/ending "-"
        *   @var string $identifier Identifier to clean for unwanted characters
        *   @return string Cleaned string
        */
        private function clean_identifier($identifier){
            $return = preg_replace('/^-+|-+$/', '', preg_replace('/[^a-z0-9]+/', '-', strtolower($identifier)));
            $this->logg('clean_identifier(): ' . $identifier . ' ---> ' . $return);
            return $return;
        }
        
        
       /**
        *   Destruct function. Prints debug information if debug is activated
        *   @return none
        */
        public function __destruct(){
            if($this->debug === true){
                var_dump($this);
            }
        }
    }
?>
