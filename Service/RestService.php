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
        throw new \Exception();
      break;
      }
    }
    return $file_contents;
  }
}
