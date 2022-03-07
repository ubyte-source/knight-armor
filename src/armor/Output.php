<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\CustomException;
use Knight\armor\output\Data;

/* Output is a class that is used to output data to the user */

class Output
{
    use Configuration;

    const CONFIGURATION_JSON_OPTION = 0x4718;

    const APIDATA = 'data';

    protected static $data;          // Data
    protected static $json_override; // (int)

    final protected function __construct() {}

    /* This is a singleton pattern. */
    
    final public static function instance() : self
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
            $instance::$data = new Data();
        }
        return $instance;
    }

    /**
     * This function will concatenate a value to a key in the data array
     * 
     * @param string key The key to store the value under.
     * @param value The value to be stored in the session.
     * @param bool overwrite If true, the value will be overwritten if it already exists.
     */
    
    public static function concatenate(string $key, $value, bool $overwrite = true) : void
    {
        static::instance();
        $data = static::getData();
        if (!property_exists($data, $key)
            || $overwrite === true) $data->$key = $value;
    }

    /**
     * Prints the output data to the screen
     * 
     * @param bool status The status of the request.
     */
    
    public static function print(bool $status = false) : void
    {
        static::instance();
        try {
            static $loop;
            if ($loop === null) $loop = 0;
            if (1 < ++$loop) {
                header('HTTP/1.0 403 Service Unavailable');
                exit;
            }

            $output_data = static::getData();
            $output_data->setStatus($status);

            $output = $output_data->out();
            $output = static::json($output);
            exit($output);
        } catch (CustomException $e) {
            usleep(256000);

            $notice = $e->getMessage();
            Output::concatenate('notice', $notice);
            Output::print(false);
        }
    }

    /**
     * * Returns a JSON encoded string
     * 
     * @param data The data to be encoded.
     * @param int options The options parameter is a bitmask of JSON encoding options. It can be used
     * to encode and decode strings with different encoding options.
     * 
     * @return A string.
     */
    
    public static function json($data, int $options = null) : string
    {
        $output_encode = $options ?? static::getEncode();
        $output = json_encode($data, $output_encode);
        if (json_last_error() !== JSON_ERROR_NONE
            || !is_string($output)) throw new CustomException('developer/output/json/encoding');

        return $output;
    }

    /**
     * *This function sets the JSON encode option override.*
     * 
     * The function is used to set the JSON encode option override. 
     * 
     * @param int option The JSON_* constant to set.
     */
    
    public static function setEncodeOptionOverride(int $option) : void
    {
        static::$json_override = $option;
    }

    /**
     * If the JSON_PRETTY_PRINT option is set, return the value of the static property json_override.
     * Otherwise, return null
     * 
     * @return The return value is a boolean value. If the value is null, then the default value is
     * used.
     */
    
    protected static function getEncodeOptionOverride() :? int
    {
        return static::$json_override;
    }

    /**
     * Returns the value of the `JSON_OPTION` configuration option
     * 
     * @return The return value is a `null` value if the configuration is not set. Otherwise, it is an
     * integer value.
     */
    
    protected static function getEncodeOptionConfiguration() :? int
    {
        return static::getConfiguration(static::CONFIGURATION_JSON_OPTION);
    }

    /**
     * Returns the encoding option to use for the current request
     * 
     * @return The value of the `getEncode` method is being returned.
     */
    
    protected static function getEncode() : int
    {
        return static::getEncodeOptionOverride() ?? static::getEncodeOptionConfiguration() ?? 0;
    }

    /**
     * This function returns the data object that is used by the application
     * 
     * @return The `getData()` method returns the `Data` object.
     */
    
    protected static function getData() : Data
    {
        return static::$data;
    }
}