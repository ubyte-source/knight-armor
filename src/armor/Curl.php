<?PHP

namespace Knight\armor;

use stdClass;

use Knight\Configuration;

use Knight\armor\Navigator;
use Knight\armor\CustomException;

class Curl
{
    use Configuration;

    const CONFIGURATION_CURL_OPTIONS = 0x12c;

    protected $header;             // (array)
    protected $return_json = true; // (bool)

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
        if (false === $this->getReturnJSON()) return $curl_response;

        $curl_response_decoded = static::JSONDecode($curl_response);
        return $curl_response_decoded;
    }

    public function setHeader(string ...$header) : self
    {
        $this->header = $header;
        return $this;
    }

    public function setReturnJSON(bool $status) : self
    {
        $this->return_json = $status;
        return $this;
    }

    protected function getReturnJSON() : bool
    {
        return $this->return_json;
    }

    protected function getHeader() :? array
    {
        return $this->header;
    }

    protected static function JSONDecode(string $string)
    {
        $string_decoded = json_decode($string);
        if (null === $string_decoded
            && json_last_error() !== JSON_ERROR_NONE) throw new CustomException('developer/curl/json/decode');

        return $string_decoded;
    }
}