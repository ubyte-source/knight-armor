<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\CustomException;

class Cipher
{
    use Configuration;

    const CONFIGURATION_METHOD = 0x64;

    protected $personal; // (string)
    protected $method;   // (string)

    public function __construct(string $method = null)
    {
        $cipher = $method ?? static::getConfiguration(static::CONFIGURATION_METHOD, true);
        $this->setMethod($cipher);
    }

    public function setKeyPersonal(string $personal) : self
    {
        $this->personal = $personal;
        return $this;
    }

    public function getKeyPersonal() : string
    {
        if (null === $this->personal) throw new CustomException('developer/cipher/set/personal');
        return md5($this->personal);
    }

    public function getIv() : string
    {
        return base64_decode($this->iv);
    }

    public function getMethod() : string
    {
        if (null === $this->method) throw new CustomException('developer/cipher/set/method');
        return $this->method;
    }

    public function getIvLenght() : int
    {
        $method = $this->getMethod();
        return openssl_cipher_iv_length($method);
    }

    public function encrypt(?string $plaintext) :? string
	{
        if (null === $plaintext) return null;
        $method = $this->getMethod();
        $personal = $this->getKeyPersonal();

        $iv = openssl_random_pseudo_bytes($this->getIvLenght());
        $raw = @openssl_encrypt($plaintext, $method, $personal, OPENSSL_RAW_DATA, $iv);
        if (false === $raw) throw new CustomException('developer/cipher/encrypt');

        $text = hash_hmac('sha256', $raw, $personal, true);
        $text = base64_encode($iv . $text . $raw);
		return $text;
    }

    public function decrypt(?string $text) :? string
	{
        if (null === $text) return null;

        $text = base64_decode($text);
        $personal = $this->getKeyPersonal();

        $iv_len = $this->getIvLenght();
        $raw = substr($text, $iv_len + 32);
        $iv = substr($text, 0, $iv_len);

        $method = $this->getMethod();
        $plaintext = @openssl_decrypt($raw, $method, $personal, OPENSSL_RAW_DATA, $iv);
        if (false === $plaintext) return null;

        $hash = hash_hmac('sha256', $raw, $personal, true);
        $text_hash = substr($text, $iv_len, 32);
        if (hash_equals($hash, $text_hash)) return $plaintext;

		return $text;
    }

    protected function setMethod(string $method) : self
    {
        $supported_methods = openssl_get_cipher_methods();
        if (!in_array($method, $supported_methods)) throw new CustomException('developer/cipher/method/set/' . $method);

        $this->method = $method;
        return $this;
    }
}