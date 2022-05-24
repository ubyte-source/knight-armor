<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\CustomException;

/* The Cipher class is a class that encrypts and decrypts data using the OpenSSL library */

class Cipher
{
    use Configuration;

    const CONFIGURATION_METHOD = 0x64;

    protected $personal; // (string)
    protected $method;   // (string)

    /**
     * The constructor takes a method as a parameter. If no method is provided, it will use the default
     * method
     * 
     * @param string method The cipher method to use.
     */
    
    public function __construct(string $method = null)
    {
        $cipher = $method ?? static::getConfiguration(static::CONFIGURATION_METHOD, true);
        $this->setMethod($cipher);
    }

    /**
     * * Set the value of the `personal` property
     * 
     * @param string personal The personal key to use for the API call.
     * 
     * @return The object itself.
     */
    
    public function setKeyPersonal(string $personal) : self
    {
        $this->personal = $personal;
        return $this;
    }

    /**
     * *Get the key for the personal cipher.*
     * 
     * The function is pretty simple. It checks to see if the personal cipher has been set. If it has,
     * it returns the MD5 hash of the personal cipher. If it hasn't, it throws an exception
     * 
     * @return The MD5 hash of the personal key.
     */
    
    public function getKeyPersonal() : string
    {
        if (null === $this->personal) throw new CustomException('developer/cipher/set/personal');
        return md5($this->personal);
    }

    /**
     * Returns the IV used to encrypt the data
     * 
     * @return The IV is being returned as a base64 encoded string.
     */
    
    public function getIv() : string
    {
        return base64_decode($this->iv);
    }

    /**
     * Get the method used for encryption
     * 
     * @return The method name.
     */
    
    public function getMethod() : string
    {
        if (null === $this->method) throw new CustomException('developer/cipher/set/method');
        return $this->method;
    }

    /**
     * Returns the length of the IV for the current cipher method
     * 
     * @return The length of the initialization vector (IV) for the given method.
     */
    
    public function getIvLenght() : int
    {
        $method = $this->getMethod();
        return openssl_cipher_iv_length($method);
    }

    /**
     * Encrypts the plaintext using the key and returns the encrypted text
     * 
     * @param plaintext The text to be encrypted.
     * 
     * @return The encrypted text.
     */
    
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

    /**
     * Decrypts the text using the personal key and the method specified in the constructor
     * 
     * @param text The encrypted text to decrypt.
     * 
     * @return The decrypted text.
     */
    
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

    /**
     * * Set the cipher method
     * 
     * @param string method The cipher method to use.
     * 
     * @return The object itself.
     */
    
    protected function setMethod(string $method) : self
    {
        $supported_methods = openssl_get_cipher_methods();
        if (!in_array($method, $supported_methods)) throw new CustomException('developer/cipher/method/set/' . $method);

        $this->method = $method;
        return $this;
    }
}
