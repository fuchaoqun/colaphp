<?php
/**
 * Parses YAML strings to PHP arrays.
 *
 */

require_once COLA_DIR . '/Com/Yaml/sfYaml.php';

class Cola_Com_Yaml
{
    /**
     * Load YAML string of file
     *
     * @param string $input
     * @return array
     */
    public static function load($input)
    {
        return sfYaml::load($input);
    }

    /**
     * Dump into YAML string or file
     *
     * @param array $data
     * @param string $file
     * @param int $inline
     * @return mixed string when sucess or false when fail
     */
    public static function dump($data, $file = null, $inline = 2)
    {
        $yaml = sfYaml::dump($data, $inline);

        if (empty($file) || file_put_contents($yaml, $file)) {
            return $yaml;
        }

        return false;
    }
}