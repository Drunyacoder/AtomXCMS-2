<?php
##################################################
##												##
## Author:       Andrey Brykin (Drunya)         ##
## Version:      0.8                            ##
## Project:      CMS                            ##
## package       CMS Fapos                      ##
## subpackege    Cache library                  ##
## copyright     ©Andrey Brykin 2010-2011       ##
##################################################


##################################################
##												##
## any partial or not partial extension         ##
## CMS Fapos,without the consent of the         ##
## author, is illegal                           ##
##################################################
## Любое распространение                        ##
## CMS Fapos или ее частей,                     ##
## без согласия автора, является не законным    ##
##################################################



define ('CACHE_ALL', 'All');
define ('CACHE_OLD', 'Old');
define ('CACHE_MATCHING_TAG', 'Match');
define ('CACHE_NOT_MATCHING_TAG', 'Not_match');
define ('CACHE_MATCHING_ANY_TAG', 'Any');



/**
* File Cache class
*
* @author       ©Andrey Brykin 2009-2011
* @package      CMS Fapos
* @subpackage   File Cache
* @link         http://cms.develdo.com
*/
Class Cache {

	/**
	* @output variable contaise output data
	*/
	public $output = '';	
	/**
	* file refix - @prefix--@fele
	*/
	public $prefix = 'page';	
	/**
	* max nesting level
	*/
	public $dirLevel = 1;	
	/**
	*	dir for cache
	*/
	public $cacheDir = 'sys/cache/';	
	/**
	* do you want lock files on changes
	*/
	public $lockFile = true;	
	/**
	* default chmod for cache dirs and files
	*/
	public $chmod = 0755;	
	/**
	* max metadata params for cache
	*/
	public $maxMetaTag = 50;	
	/**
	* default tags 
	*/
	public $metaArray = array();	
	/**
	* lifetime cache in seconds
	*/
	public $lifeTime = 10800;

	//compact variant
	public $option = array(
		'lifeTime' => 10800,
		'metaArray' => array(),
		'maxMetaTag' => 50,
		'chmod' => 0755,
		'lockFile' => true,
		'cacheDir' => 'sys/cache/',
		'dirLevel' => 1,
		'prefix' => 'page',
	);
	
	
	
	/**
	* may be needed to future
	*/
	
	public function __construct() {
		$this->cacheDir = ROOT . '/sys/cache/';
	}
	
	/**
	* read cache
	*/
	public function read($id) {
		$file = $this->_getFile($id);
		$data = $this->_getContents($file);
		
		return $data;
	}
	
	/**
	* save cache data and meta
	*/
	public function write($data, $id, $params = array(), $specificParams = array()) {
		
		$file = $this->_getFile($id);
		$path = $this->_path($id);
		$specificLifeTime = (!empty($specificParams['lifetime'])) ? intval($specificParams['lifetime']) : null;
		
		
		if (!is_writable($path)) {
		
			$this->_recursiveMkdirAndChmod($id);
		}
		if (!is_writable($path)) {
			return false;
		}
		
		$meta = array(
			'hash' => crc32($data),
			'tags' => $params,
			'expire' => $this->_getExpireTime($specificLifeTime),
			'mtime' => time(),
		);
		
		$res = $this->_writeCache($file, $data);
		if (!$res) {
			return false;
		}
		$res = $this->_saveMeta($id, $meta);
		return $res;
		
	}
	
	
	/**
	* delete cache file
	*/
    public function remove($id) {
        $file = $this->_getFile($id);
        $boolRemove   = $this->_remove($file);
        $boolMetadata = $this->_delMeta($id);
        return $boolMetadata && $boolRemove;
    }
	
	
	
    public function clean($mode = CACHE_ALL, $tags = array())  {
        // We use this protected method to hide the recursive stuff
        clearstatcache();
        return $this->_clean($this->cacheDir, $mode, $tags);
    }
	
	
	/**
	* clean cache
	*/
    protected function _clean($dir, $mode = CACHE_ALL, $tags = array()) {
		
        if (!is_dir($dir)) {
            return false;
        }
        $result = true;
        $prefix = $this->prefix;
				
        $glob = @glob($dir . $prefix . '__*');
        if ($glob === false) {
            return true;
        }

        foreach ($glob as $file)  {
            if (is_file($file)) {
                $fileName = basename($file);
                if ($this->_isMetaFile($fileName)) {
                    // in CLEANING_MODE_ALL, we drop anything, even remainings old metadatas files
                    if ($mode != 'ALL') {
                        continue;
                    }
                }
                $id = $this->_fileNameToId($fileName);
                $metadatas = $this->_getMeta($id);
                if ($metadatas === FALSE) {
                    $metadatas = array('expire' => 1, 'tags' => array());
                }
                switch ($mode) {
                    case CACHE_ALL:
                        $res = $this->remove($id);
                        if (!$res) {
                            // in this case only, we accept a problem with the metadatas file drop
                            $res = $this->_remove($file);
                        }
                        $result = $result && $res;
                        break;
                    case CACHE_OLD:
                        if (time() > $metadatas['expire']) {
                            $result = $this->remove($id) && $result;
                        }
                        break;
                    case CACHE_MATCHING_TAG:
                        $matching = true;
                        foreach ($tags as $tag) {
                            if (!in_array($tag, $metadatas['tags'])) {
                                $matching = false;
                                break;
                            }
                        }
                        if ($matching) {
                            $result = $this->remove($id) && $result;
                        }
                        break;
                    case CACHE_NOT_MATCHING_TAG:
                        $matching = false;
                        foreach ($tags as $tag) {
                            if (in_array($tag, $metadatas['tags'])) {
                                $matching = true;
                                break;
                            }
                        }
                        if (!$matching) {
                            $result = $this->remove($id) && $result;
                        }
                        break;
                    case CACHE_MATCHING_ANY_TAG:
                        $matching = false;
                        foreach ($tags as $tag) {
                            if (in_array($tag, $metadatas['tags'])) {
                                $matching = true;
                                break;
                            }
                        }
                        if ($matching) {
                            $result = $this->remove($id) && $result;
                        }
                        break;
                    default:
                       // Zend_Cache::throwException('Invalid mode for clean() method');
                        break;
                }
            }
            if ((is_dir($file)) and ($this->dirLevel > 0)) {
                // Recursive call
                $result = $this->_clean($file . DS, $mode, $tags) && $result;
                if ($mode=='all') {
                    // if mode=='all', we try to drop the structure too
                    @rmdir($file);
                }
            }
        }
        return $result;
    }
	
	
	/**
	* check cashe file on expire date
	*/
    public function check($id, $extraLifetime = 343) {

        $metadatas = $this->_getMeta($id);
		
        if (!$metadatas) {
            return false;
        }
        if (time() > $metadatas['expire']) {
			$this->remove($id);
            return false;
        }
        return true;
    }
	
	
	
	/**
	* return expire time
	*/
	protected function _getExpireTime($specificLifeTime = null) {
		if (empty($specificLifeTime) || !is_numeric($specificLifeTime)) return (time() + $this->lifeTime);
		return (time() + $specificLifeTime);
	}
	
	/**
	* check meta file
	*/ 
    protected function _isMetaFile($fileName) {
        $id = $this->_fileNameToId($fileName);
        if (substr($id, 0, 21) == 'meta__') {
            return true;
        } else {
            return false;
        }
    }
	
	/**
	* delete metadatas file
	*/
    protected function _delMeta($id) {
        if (isset($this->_metaArray[$id])) {
            unset($this->_metaArray[$id]);
        }
        $file = $this->_metaFile($id);
        return $this->_remove($file);
    }
	
	
	
	/**
	* get metadatas
	*/
    protected function _getMeta($id) {
        if (isset($this->_metaArray[$id])) {
            return $this->_metaArray[$id];
        } else {
            $metadatas = $this->_loadMeta($id);
            if (!$metadatas) {
                return false;
            }
            $this->_setMeta($id, $metadatas, false);
            return $metadatas;
        }
    }
	
	
	/**
	* load metadatas from file
	*/
    protected function _loadMeta($id) {
        $file = $this->_metaFile($id);
        $result = $this->_getContents($file);
        if (!$result) {
            return false;
        }
        $tmp = @unserialize($result);
        return $tmp;
    }
	
	
	/**
	* save MetaData
	*/
    protected function _saveMeta($id, $meta) {
        $file = $this->_metaFile($id);
        $result = $this->_writeCache($file, serialize($meta));
        if (!$result) {
            return false;
        }
        return true;
    }
	
	
	
    protected function _metaFile($id)  {
        $path = $this->_path($id);
        $fileName = $this->_getFileId('meta__' . $id);
        return $path . $fileName;
    }
	
	
	/**
	* set metadatas
	*/
    protected function _setMeta($id, $metadatas, $save = true) {
        if (count($this->metaArray) >= $this->maxMetaTag) {
            $n = (int) ($this->maxMetaTag / 10);
            $this->metaArray = array_slice($this->metaArray, $n);
        }
        if ($save) {
            $result = $this->_saveMeta($id, $metadatas);
            if (!$result) {
                return false;
            }
        }
        $this->metaArray[$id] = $metadatas;
        return true;
    }
	
	
	
	protected function _remove($file) {
        if (!is_file($file)) {
            return false;
        }
        if (!@unlink($file)) {
            return false;
        }
        return true;
    }
	
	
	/**
	* write content into cache file
	*/ 
	protected function _writeCache($file, $data) {
        $result = false;
        $f = @fopen($file, 'ab+');
        if ($f) {
            if ($this->lockFile) @flock($f, LOCK_EX);
            fseek($f, 0);
            ftruncate($f, 0);
            $tmp = @fwrite($f, $data);
            if (!($tmp === false)) {
                $result = true;
            }
            @fclose($f);
        }
        @chmod($file, $this->chmod);
        return $result;
	}
	
	
	/**
	* create dirs and set CHMOD
	*/
    protected function _recursiveMkdirAndChmod($id) {
        if ($this->dirLevel <=0) {
            return true;
        }
        $parts = $this->_path($id, true);
        foreach ($parts as $part) {
		
            if (!is_dir($part)) {
		
                @mkdir($part, $this->chmod, true);
                @chmod($part, $this->chmod); // see #ZF-320 (this line is required in some configurations)
            }
        }
        return true;
    }
	
	
	
	/**
	* get cache file content
	*/
	protected function _getContents($file) {
		if (!is_file($file)) return false;
		
        $f = @fopen($file, 'rb');
        if ($f) {
            if ($this->lockFile) @flock($f, LOCK_SH);
            $result = stream_get_contents($f);
            if ($this->lockFile) @flock($f, LOCK_UN);
            @fclose($f);
        }
		return (!empty($result)) ? $result : false;
	}
	
	
	/**
	* return full path with filename
	* @id unique cache id
	*/
	protected function _getFile($id) {
		$path = $this->_path($id);
		$file = $this->_getFileId($id);
		return $path . $file;
	}
	
	
	
	/**
	* return unique id for cache file
	* @id unique cache id
	*/
	protected function _getFileId($id) {
		return $this->prefix . '__' . $id;
	}
	
	
	/**
	* return path to cashe file
	* @id unique cache id
	*/
	protected function _path($id, $parts = false) {
		$root = $this->cacheDir;
		$prefix = $this->prefix;
		if ($this->dirLevel > 0) {
			$hash = $this->_hash($id, 'md5');
			for ($i = 0; $i < $this->dirLevel; $i++) {
				$root = $root . $prefix . '__' . substr($hash, 0, $i + 1) . DS;
				$partsArray[] = $root;
			}
		}
		
        if ($parts) {
            return $partsArray;
        } else {
            return $root;
        }
	}
	
	
	/**
	* create hash
	* @hashType type of hash (md5, crc32, ....);
	*/
    protected function _hash($data, $hashType) {
        switch ($hashType) {
        case 'md5':
            return md5($data);
        case 'crc32':
            return crc32($data);
        case 'strlen':
            return strlen($data);
        case 'adler32':
            return hash('adler32', $data);
        default:
            return md5($data);
        }
    }
	
	
	/**
	*
	*/
    protected function _fileNameToId($fileName) {
        $prefix = $this->prefix;
        return preg_replace('~^' . $prefix . '__(.*)$~', '$1', $fileName);
    }
	

	/**
	* have or not have meta file
	*/
    protected function _isMetadatasFile($fileName)
    {
        $id = $this->_fileNameToId($fileName);
		echo substr($id, 0, 6);
        if (substr($id, 0, 6) == 'meta__') {
            return true;
        } else {
            return false;
        }
    }
	
}



