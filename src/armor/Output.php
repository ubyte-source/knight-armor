<?PHP

namespace Knight\armor;

use Knight\Configuration;

use Knight\armor\CustomException;
use Knight\armor\output\Data;

class Output
{
    use Configuration;

    const CONFIGURATION_JSON_OPTION = 0x4718;

    const APIDATA = 'data';

    protected static $data;          // Data
    protected static $json_override; // (int)

    final protected function __construct() {}

    final public static function instance() : self
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
            $instance::$data = new Data();
        }
        return $instance;
    }

    public static function concatenate(string $key, $value, bool $overwrite = true) : void
    {
        static::instance();
        $data = static::getData();
        if (!property_exists($data, $key)
            || $overwrite === true) $data->$key = $value;
    }

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

    public static function json($data, int $options = null) : string
    {
        $output_encode = $options ?? static::getEncode();
        $output = json_encode($data, $output_encode);
        if (json_last_error() !== JSON_ERROR_NONE
            || !is_string($output)) throw new CustomException('developer/output/json/encoding');

        return $output;
    }

    public static function setEncodeOptionOverride(int $option) : void
    {
        static::$json_override = $option;
    }

    protected static function getEncodeOptionOverride() :? int
    {
        return static::$json_override;
    }

    protected static function getEncodeOptionConfiguration() :? int
    {
        return static::getConfiguration(static::CONFIGURATION_JSON_OPTION);
    }

    protected static function getEncode() : int
    {
        return static::getEncodeOptionOverride() ?? static::getEncodeOptionConfiguration() ?? 0;
    }

    protected static function getData() : Data
    {
        return static::$data;
    }
}