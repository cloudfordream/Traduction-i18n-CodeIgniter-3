<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class My_Lang
 * 
 * @author Maxime Delalande
 * @link https://github.com/cloudfordream
 */

class MY_Lang extends CI_Lang {

    private $config;

    private $language_abbr;

    private $default_language;

    private $default_language_abbr;

    private $language_available;

    private $language_uri_ignore;

    private $uri_abbr;

    private $query_string;

    public function __construct()
    {
        parent::__construct();

        global $URI, $CFG, $IN;

        $this->config =& $CFG->config;

        $this->language_abbr = $this->config['language_abbr'];

        $this->default_language = $this->config['language'];

        $this->default_language_abbr = $this->config['language_abbr'];

        $this->language_available = $this->config['language_available'];

        $this->language_uri_ignore = $this->config['language_uri_ignore'];

        $this->uri_abbr = (strlen($URI->segment(1)) === 2) ? $URI->segment(1) : NULL;

        $this->query_string = (!empty($IN->server('QUERY_STRING'))) ? '?' . $IN->server('QUERY_STRING') : NULL;

        /**
         * Control if uri must be ignored
         */
        foreach($this->language_uri_ignore as $uri){
            if(preg_match('/' . preg_replace('/\//', '\/', $uri) . '/i', $URI->uri_string()) === 0 OR is_null($uri)){
                /**
                 * The uri should not be ignored
                 */
                $ignore = FALSE;
            }else{
                /**
                 * Uri should be ignored
                 */
                $ignore = TRUE;

                break;
            }
        }

        if(!$ignore){
            /**
             * Control cookie if set
             */
            if(!empty($IN->cookie('user_language', TRUE))){
                /**
                 * Cookie exist
                 */
                if(!array_key_exists($IN->cookie('user_language', TRUE), $this->language_available)){
                    /**
                     * If lang in cookie isn't available set default abbr
                     */
                    $this->language_abbr = $this->default_language_abbr;
                }else{
                    /**
                     * If lang in cookie is avaible set abbr
                     */
                    $this->language_abbr = $IN->cookie('user_language', TRUE);
                }
            }else{
                /**
                 * Cookie dosen't exist
                 */
                if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND array_key_exists(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2), $this->language_available)){
                    /**
                     * If user browser language is available set abbr
                     */
                    $this->language_abbr = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                }else{
                    /**
                     * If user browser language isn't available set default abbr
                     */
                    $this->language_abbr = $this->default_language_abbr;
                }
            }

            /**
             * Control if language is set in url
             */
            if(!empty($this->uri_abbr)){
                /**
                 * Language is set, control if available
                 */
                if(!array_key_exists($this->uri_abbr, $this->language_available)){
                    /**
                     * Language isn't available, redirect user to default language
                     */
                    $URI->uri_string = preg_replace('/^\/?' . $this->uri_abbr . '/', $this->default_language_abbr, $URI->uri_string);

                    $this->set_language($this->default_language_abbr);

                    header('Location: '.$this->config['base_url'].$URI->uri_string.$this->query_string);

                    exit();
                }else{
                    /**
                     * Language is available set cookie
                     */
                    $this->set_language($this->uri_abbr);
                }
            }else{
                /**
                 * Set language in cookie
                 */
                $this->set_language($this->language_abbr);

                header('Location: '.$this->config['base_url'].$this->language_abbr.'/'.$URI->uri_string.$this->query_string);

                exit();
            }

            log_message('debug', "Language i18n Class Initialized");

            /**
             * Control if language is set in url and is end by slash
             */
            if(strlen($this->uri_abbr) === 2 AND empty($URI->segment(2)) AND substr($IN->server('REQUEST_URI'), -1) !== '/'){
                header('Location: '.$this->config['base_url'].$this->uri_abbr.'/'.$this->query_string);
            }
        }
    }

    /**
     * Create cookie for language
     * 
     * @param string $language_abbr
     */
    private function set_language(string $language_abbr)
    {
        global $IN;

        $IN->set_cookie('user_language', $language_abbr, $this->config['language_save_time']);

        $this->config['language'] = $this->language_available[$language_abbr];
        $this->config['language_abbr'] = $language_abbr;
    }

    /**
     * Retrieve current user language
     * 
     * @return string
     */

    public function language(): string
    {
        return $this->config['language_abbr'];
    }

    /**
     * Language switcher
     * 
     * @param string $language_abbr
     * @param string $uri
     * 
     * @return string
     */

    public function switch_language(string $language_abbr, string $uri = NULL): string
    {
        global $URI;

        if(!is_null($uri)){
            $URI->uri_string = base_url() . $language_abbr . '/' . $uri;
        }else{
            $URI->uri_string = preg_replace('/^\/?' . $this->uri_abbr . '/', $language_abbr, $URI->uri_string . $this->query_string);
        }

        return $URI->uri_string;
    }
    
    /**
     * Helper for retrieve text translation
     * 
     * @param string $line
     * @param array $dynamics Use this format : ['dynamics_key' => 'value']
     * 
     * @return string
     */

    public function translation(string $line, array $dynamics = []): string
    {
        global $LANG;

        if(isset($dynamics)){
            $array = $dynamics;

            $return = preg_replace_callback('/{(.*?)}/', function($matches) use ($array){
                $match = $matches[0];
                $name = $matches[1];

                return isset($array[$name]) ? $array[$name] : $match;
            }, $LANG->line($line));
        }else{
            $return = ($translation = $LANG->line($line)) ? $translation : $line;
        }

        return $return;
    }

    /**
     * Helper for retrieve url in current language
     * 
     * @param string $line
     * @param array $dynamics 
     * 
     * Use this format : 
     *  [
     *      'fr' => [
     *          'dynamics_key' => 'value'    
     *      ],
     *      'en' => [
     *          'dynamics_key' => 'value'    
     *      ]
     *  ]
     * 
     * @return string
     */

    public function url(string $line, array $dynamics = []): string
    {
        global $LANG;

        if(isset($dynamics[$this->language()])){
            $array = $dynamics[$this->language()];

            $return = preg_replace_callback('/{(.*?)}/', function($matches) use ($array){
                $match = $matches[0];
                $name = $matches[1];

                return isset($array[$name]) ? $array[$name] : $match;
            }, base_url() . $this->language() . '/' . $LANG->line($line));               
        }else{
            $return = ($url = $LANG->line($line)) ? base_url() . $this->language() . '/' . $url : $line;
        }

        return $return;
    }

    /**
     * Helper for retrieve url in all language
     * 
     * @param string $line
     * @param array $dynamics
     * 
     * Use this format : 
     *  [
     *      'fr' => [
     *          'dynamics_key' => 'value'    
     *      ],
     *      'en' => [
     *          'dynamics_key' => 'value'    
     *      ]
     *  ]
     * 
     * @return array
     */

    public function url_list(string $line, array $dynamics = []): array
    {
        global $CFG;

        foreach($CFG->config['language_available'] as $language_abbr => $language_name){
            require APPPATH . 'language/' . $language_name . '/core/url_lang.php';

            if(isset($dynamics[$language_abbr])){
                $array = $dynamics[$language_abbr];

                $return[$language_abbr] = preg_replace_callback('/{(.*?)}/', function($matches) use ($array){
                    $match = $matches[0];
                    $name = $matches[1];

                    return isset($array[$name]) ? $array[$name] : $match;
                }, base_url() . $language_abbr . '/' . $lang[$line]);
            }else{
                $return[$language_abbr] = (!empty($lang[$line])) ? $lang[$line] : NULL;
            }
        }

        return $return;
    }

}

?>
