<?php
namespace Itembase\Psdk\Http;

/**
 * Class HttpClient
 *
 * Simple HTTP client for communicating with itembase server.
 *
 * @package       Itembase\Psdk\Http
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class HttpClient
{
    /**
     * Method to send request to itembase server. If "data" is passed POST request will be sent, otherwise GET request
     * is send.
     *
     * @param string     $url    URL where request should be sent
     * @param mixed|null $data   data which needs to be send
     * @param array      $header HTTP headers
     *
     * @return mixed|string
     *
     * @throws \Exception
     */
    public function sendData($url, $data = null, $header = array())
    {
        if (extension_loaded('curl')) {
            $ibCurl = curl_init();

            curl_setopt($ibCurl, CURLOPT_HEADER, true);
            curl_setopt($ibCurl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ibCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ibCurl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ibCurl, CURLOPT_VERBOSE, 1);
            curl_setopt($ibCurl, CURLOPT_URL, $url);
            curl_setopt($ibCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ibCurl, CURLOPT_CONNECTTIMEOUT, 30);

            if ($data) {
                $prepareData = http_build_query($data);

                curl_setopt($ibCurl, CURLOPT_POST, true);
                curl_setopt($ibCurl, CURLOPT_POSTFIELDS, $prepareData);
            }

            $response    = curl_exec($ibCurl);
            $header_size = curl_getinfo($ibCurl, CURLINFO_HEADER_SIZE);
            $response    = substr($response, $header_size);
            $httpInfo    = curl_getinfo($ibCurl);

            if ($response === false || $response == '' || $httpInfo['http_code'] == 500 || $httpInfo['http_code'] == 404) {
                $errorMsg = curl_error($ibCurl);
                curl_close($ibCurl);

                throw new \Exception(sprintf(
                    'curl error: %s, http status code: %s, requested URI: %s',
                    $errorMsg,
                    $httpInfo['http_code'],
                    $url
                ));
            }

            curl_close($ibCurl);
        } else {
            $dataQuery = '';
            $opts      = array('http' => array('ignore_errors' => true, 'timeout' => 5));
            $context   = stream_context_create($opts);

            if ($data) {
                $dataQuery = '?' . http_build_query($data);;
            }

            $response = file_get_contents($url . $dataQuery, false, $context);

            if ($response === false) {
                throw new \Exception('file_get_contents error.');
            }
        }

        return $response;
    }

    /**
     * Method to send request to itembase server. If "data" is passed POST request will be sent, otherwise GET request
     * is send.
     *
     * @param string     $url    URL where request should be sent
     * @param mixed|null $data   data which needs to be send
     *
     * @return mixed|string
     *
     * @throws \Exception
     */
    public function sendJsonData($url, $data = null)
    {
        if (extension_loaded('curl')) {
            $ibCurl = curl_init();

            curl_setopt($ibCurl, CURLOPT_HEADER, true);
            curl_setopt($ibCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ibCurl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ibCurl, CURLOPT_VERBOSE, 1);
            curl_setopt($ibCurl, CURLOPT_URL, $url);
            curl_setopt($ibCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ibCurl, CURLOPT_CONNECTTIMEOUT, 30);

            if ($data) {
                $json = json_encode($data);

                curl_setopt($ibCurl, CURLOPT_POST, true);
                curl_setopt($ibCurl, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ibCurl, CURLOPT_POSTFIELDS, $json);
                curl_setopt($ibCurl, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json",
                    "Content-Length: " . strlen($json),
                ));
            }

            $response    = curl_exec($ibCurl);
            $header_size = curl_getinfo($ibCurl, CURLINFO_HEADER_SIZE);
            $response    = substr($response, $header_size);
            $httpInfo    = curl_getinfo($ibCurl);

            if ($response === false || $response == '' || $httpInfo['http_code'] == 500 || $httpInfo['http_code'] == 404) {
                $errorMsg = curl_error($ibCurl);
                curl_close($ibCurl);

                throw new \Exception(sprintf(
                    'curl error: %s, http status code: %s, requested URI: %s',
                    $errorMsg,
                    $httpInfo['http_code'],
                    $url
                ));
            }

            curl_close($ibCurl);
        } else {
            throw new \Exception("No curl extensions");
        }

        return $response;
    }
}
