<?php
namespace Striide\RestBundle\Service;
use Striide\RestBundle\Exceptions\PageNotFoundException;
use Striide\RestBundle\Exceptions\ServerErrorException;

class RestService
{
  private $logger = null;
  public function __construct($logger)
  {
    $this->logger = $logger;
  }

  public function download($url,$user_agent = null)
  {
    $filename = dirname(__FILE__) . "/file.download";

    $this->logger->info(sprintf("RestService->get(%s)", $url));
    $timeout = 0; // set to zero for no timeout

    set_time_limit(0);
    $fp = fopen($filename, 'w+');//This is the file where we save the    information
    $ch = curl_init(str_replace(" ","%20",$url));//Here is the file we are downloading, replace spaces with %20
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_FILE, $fp); // here it sais to curl to just save it
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($ch);//get curl response
    curl_close($ch);
    fwrite($fp, $data);//write curl response to file
    fclose($fp);
    return $filename;
  }

  public function get($url, $user_agent = null)
  {
    $this->logger->info(sprintf("RestService->get(%s)", $url));
    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    if (!is_null($user_agent))
    {
      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    }
    $file_contents = curl_exec($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);

    if ($header['http_code'] != 200)
    {

      switch ($header['http_code'])
      {
      case '500':
        throw new PageNotFoundException();
      break;
      case '404':
        throw new PageNotFoundException();
      break;
      default:
        throw new \Exception(sprintf("Rest call failed: %s",$url));
      break;
      }
    }
    return $file_contents;
  }
}
