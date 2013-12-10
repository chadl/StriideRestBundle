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

  /**
   *
   */
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

  /**
   *
   */
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

  /**
   *
   */
  public function post($url, $params = array(), $user_agent = null)
  {
    $this->logger->info(sprintf("RestService->post(%s)", $url, $params));
    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

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
  
  
  /**
   *
   */
  public function raw($url, $method = 'GET', $params = array(), $headers = array())
  {
    $this->logger->info(sprintf("%s", __METHOD__), array( $url, $method, $params, $headers));
    $ch = curl_init();
    $timeout = 0; // set to zero for no timeout
    curl_setopt($ch, CURLOPT_URL, $url);
    
    $filename = "/dev/null";
    $fp = fopen($filename, 'w+');//This is the file where we save the    information
    curl_setopt($ch, CURLOPT_FILE, $fp); // here it sais to curl to just save it
    
    curl_setopt($ch,CURLOPT_PORT,443);
    
    if($method == "POST")
    {
      curl_setopt($ch, CURLOPT_POST, 1);
    }
    if($method == "PUT")
    {
      curl_setopt($ch, CURLOPT_PUT, 1);
    }
    
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));

    $file_contents = curl_exec($ch);
    $header = curl_getinfo($ch);
    curl_close($ch);
    
    fwrite($fp, $file_contents);//write curl response to file
    fclose($fp);

    if ($header['http_code'] != 200)
    {

      switch ($header['http_code'])
      {
        case '500':
          throw new ServerErrorException();
        break;
        case '404':
          throw new PageNotFoundException();
        break;
        default:
          $this->logger->info("wha?", $header);
          throw new \Exception(sprintf("Rest call failed: %s....  status code is: %s",$url, $header['http_code']));
      }
    }
    return $file_contents;
  }
}
