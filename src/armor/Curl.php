<?PHP

namespace Knight\armor;

use stdClass;

use Knight\Configuration;

use Knight\armor\Request;
use Knight\armor\Navigator;
use Knight\armor\CustomException;

/* The Curl class is a wrapper for the PHP cURL library */

class Curl
{
    use Configuration;

    const CONFIGURATION_CURL_OPTIONS = 0x12c;

    protected $header;             // (array)
    protected $return_json = true; // (bool)

    /**
     * It makes a request to a URL and returns the response.
     * 
     * @param string get The URL to request.
     * @param post The post data to send to the URL.
     * 
     * @return The response from the request.
     */

    public function request(string $get, $post = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $get);

        $curl_options = static::getConfiguration(static::CONFIGURATION_CURL_OPTIONS);
        if (is_array($curl_options)) foreach ($curl_options as $constant => $value) {
            $value_assign = $constant !== CURLOPT_USERAGENT || $value !== Navigator::HTTP_USER_AGENT ? $value : Navigator::getUserAgent();
            curl_setopt($curl, $constant, $value_assign);
        }

        $header = $this->getHeader();
        if (null !== $header) curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        if (null !== $post) {
            $curl_post = is_array($post) ? http_build_query($post) : $post;
            curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post);
            curl_setopt($curl, CURLOPT_POST, 1);
        }

        $curl_response = curl_exec($curl);
        $curl_response_info = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $curl_response_info) throw new CustomException('developer/curl/' . $curl_response_info);

        return $this->getReturnJSON()
            ? Request::JSONDecode($curl_response)
            : $curl_response;
    }

    /**
     * *This function sets the header for the HTTP request.*
     * 
     * The function takes in a variable number of arguments. The first argument is the header name, and
     * the second argument is the header value. The function returns the current instance of the class
     * 
     * @return The object itself.
     */
    
    public function setHeader(string ...$header) : self
    {
        $this->header = $header;
        return $this;
    }

    /**
     * *This function sets the return_json property to the value of the status parameter.*
     * 
     * *This function is used to set the return_json property to true or false.*
     * 
     * *The return_json property is used to determine whether or not the output of the function is in
     * JSON format.*
     * 
     * @param bool status Whether or not to return JSON.
     * 
     * @return The object itself.
     */
    
    public function setReturnJSON(bool $status) : self
    {
        $this->return_json = $status;
        return $this;
    }

    /**
     * Returns the value of the `return_json` property
     * 
     * @return The return_json property is being set to true.
     */
    
    protected function getReturnJSON() : bool
    {
        return $this->return_json;
    }

    /**
     * Returns the header of the request
     * 
     * @return The header property.
     */
    
    protected function getHeader() :? array
    {
        return $this->header;
    }
}