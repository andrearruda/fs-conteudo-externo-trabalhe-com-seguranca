<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;
use FileSystemCache;
use Thapp\XmlBuilder\XMLBuilder;
use Thapp\XmlBuilder\Normalizer;

final class WorkplaceSafety
{
    private $fileXML = __DIR__ . '/../../../data/trabalhe_com_seguranca.xml';

    public function __invoke(Request $request, Response $response, $args)
    {
        $amount = isset($args['amount']) ? $args['amount'] : 5;
        $forceFileCached = isset($request->getQueryParams()['forceFileCached']) ? $request->getQueryParams()['forceFileCached'] : false;

        FileSystemCache::$cacheDir = __DIR__ . '/../../../cache/tmp';
        $key = FileSystemCache::generateCacheKey('cache', null);
        $data = FileSystemCache::retrieve($key);

        if($data === false || $forceFileCached == true)
        {
            $json = json_decode(json_encode(simplexml_load_file($this->getFileXML())), true);
            $json = $json['item'];

            $data = array();
            for($i = 0; $i < $amount; $i++)
            {
                $indice = rand(0, (count($json)-1));
                $data[] = array(
                    'title' => $json[$indice]['title'],
                    'description' => $json[$indice]['description'],
                    'image' => $this->getPathImages() . $json[$indice]['image']
                );

                unset($json[$indice]);
                shuffle($json);
            }

            FileSystemCache::store($key, $data, 259200);
        }

        $xmlBuilder = new XmlBuilder('root');
        $xmlBuilder->load($data);
        $xml_output = $xmlBuilder->createXML(true);
        $response->write($xml_output);
        $response = $response->withHeader('content-type', 'text/xml');
        return $response;
    }

    /**
     * @return string
     */
    public function getFileXML()
    {
        return $this->fileXML;
    }

    /**
     * @return string
     */
    public function getPathImages()
    {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/trabalhe_com_seguranca/data/images/';
    }
}