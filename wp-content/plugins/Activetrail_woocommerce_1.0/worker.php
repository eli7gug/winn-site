<?php
defined('ABSPATH') or die("Cannot access pages directly.");

class BridgeConnector
{
  var $root = '';
  var $bridgePath = '';
  var $errorMessage = '';
  var $configFilePath = '/config.php';
  var $downloadBridgePath = 'https://api.api2cart.com/v1.0/bridge.download.file?whitelabel=true';
  var $extractEntities = array(
    'bridge.php' => 'bridge2cart/bridge.php',
    'config.php' => 'bridge2cart/config.php',
    'index.php'  => 'bridge2cart/index.php'
  );

  public function __construct()
  {
    $this->root = ABSPATH;
    $this->bridgePath = $this->root . '/bridge2cart';
  }

  /**
   * @return bool
   */
  public function isBridgeExist()
  {
    if (is_dir($this->bridgePath)
      && file_exists($this->bridgePath . '/bridge.php')
      && file_exists($this->bridgePath . '/config.php')
    ) {
      return true;
    }

    return false;
  }

  /**
   * @return bool
   */
  public function installBridge()
  {
    if ($this->isBridgeExist()) {
      return true;
    }

    $status = file_put_contents("bridge.zip", self::file_get_contents($this->downloadBridgePath));
    if (!$status) {
      return false;
    }

    @mkdir($this->bridgePath, 0755);
    $zip = new \ZipArchive();
    $unzip = $zip->open("bridge.zip");

    if ($unzip === true) {
      foreach ($this->extractEntities as $fileName => $filePath) {
        if ($fpr = $zip->getStream($filePath)) {
          $fpw = fopen($this->bridgePath . DIRECTORY_SEPARATOR . $fileName, 'w');
          while ($data = fread($fpr, 1024)) {
            fwrite($fpw, $data);
          }

          fclose($fpw);
          fclose($fpr);
        }
      }
    }

    @unlink("bridge.zip");

    return true;
  }

  /**
   * @return bool
   */
  public function unInstallBridge()
  {
    if (!$this->isBridgeExist()) {
      return true;
    }

    return $this->deleteDir($this->bridgePath);
  }

  /**
   * @param $token
   *
   * @return bool
   */
  public function updateToken($token)
  {
    $config = @fopen($this->bridgePath . $this->configFilePath, 'w');

    if (!$config) {
      return false;
    }

    $writed = fwrite($config, "<?php define('M1_TOKEN', '" . $token . "');");
    if ($writed === false) {
      return false;
    }

    fclose($config);
    return true;
  }

  /**
   * @param $dirPath
   *
   * @return bool
   */
  private function deleteDir($dirPath)
  {
    if (is_dir($dirPath)) {
      $objects = scandir($dirPath);

      foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
          if (filetype($dirPath . DIRECTORY_SEPARATOR . $object) == "dir") {
            $this->deleteDir($dirPath . DIRECTORY_SEPARATOR . $object);
          } elseif (!unlink($dirPath . DIRECTORY_SEPARATOR . $object)) {
            return false;
          }
        }
      }

      reset($objects);

      if (!rmdir($dirPath)) {
        return false;
      }
    } else {
      return false;
    }

    return true;
  }

  /**
   * @return string
   */
  public static function generateStoreKey()
  {
    $bytesLength = 256;

    if (function_exists('random_bytes')) { // available in PHP 7
      return md5(random_bytes($bytesLength));
    }

    if (function_exists('mcrypt_create_iv')) {
      $bytes = mcrypt_create_iv($bytesLength, MCRYPT_DEV_URANDOM);
      if ($bytes !== false && strlen($bytes) === $bytesLength) {
        return md5($bytes);
      }
    }

    if (function_exists('openssl_random_pseudo_bytes')) {
      $bytes = openssl_random_pseudo_bytes($bytesLength);
      if ($bytes !== false) {
        return md5($bytes);
      }
    }

    if (file_exists('/dev/urandom') && is_readable('/dev/urandom')) {
      $frandom = fopen('/dev/urandom', 'r');
      if ($frandom !== false) {
        return md5(fread($frandom, $bytesLength));
      }
    }

    $rand = '';
    for ($i = 0; $i < $bytesLength; $i++) {
      $rand .= chr(mt_rand(0, 255));
    }

    return md5($rand);
  }

  /**
   * @param $url
   * @param int $timeout
   * @return bool|mixed|string
   */
  public static function file_get_contents($url, $timeout = 5)
  {
    if (in_array(ini_get('allow_url_fopen'), array('On', 'on', '1'))) {
      return @file_get_contents($url);
    } elseif (function_exists('curl_init')) {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      $content = curl_exec($curl);
      curl_close($curl);
      return $content;
    } else {
      return false;
    }
  }

}
