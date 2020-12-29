<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Lang extends CI_Lang
{

    private $config;
    
    private $lang_abbr;
    private $default_abbr;
    private $lang_available;
    private $lang_uri_ignore;

    private $uri_abbr;

    private $query_string;

    public function __construct()
    {

        parent::__construct();
        
        global $URI, $CFG, $IN;
        
        $this->config =& $CFG->config;
        
        $this->default_abbr    = $this->config['language_abbr'];
        $this->lang_available  = $this->config['lang_available'];
        $this->lang_uri_ignore = $this->config['lang_uri_ignore'];
        $this->uri_abbr        = (strlen($URI->segment(1)) === 2) ? $URI->segment(1) : NULL;
        $this->query_string    = (!empty($IN->server('QUERY_STRING'))) ? '?' . $IN->server('QUERY_STRING') : NULL;

        /* control if uri isn't in lang_uri_ignore */
        if(!in_array($URI->segment(1), $this->lang_uri_ignore, TRUE)){

            /* determine lang */
            if(empty($IN->cookie('user_lang', TRUE))){
                if(isset($this->lang_available[substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2)])){
                    $this->default_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
                }else{
                    $this->default_lang = $this->default_abbr;
                }
            }else{
                if(!isset($this->lang_available[$IN->cookie('user_lang', TRUE)])){
                    $this->default_lang = $this->default_abbr;
                }else{
                    $this->default_lang = $IN->cookie('user_lang', TRUE);
                }
            }

            /* control if lang is not set in url */
            if(empty($this->uri_abbr)){
                /* set lang in uri */    
                $this->lang_abbr .= empty($this->lang_abbr) ? $this->default_lang : "/".$this->default_lang;

                $this->set_lang($this->default_lang);

                /* redirect */
                header('Location: '.$this->config['base_url'].$this->lang_abbr.'/'.$URI->uri_string.$this->query_string);

                exit();
            }else{
                /* if lang is not available */
                if(!isset($this->lang_available[$this->uri_abbr])){
                    $URI->uri_string = preg_replace('/^\/?' . $this->uri_abbr . '/', $this->default_lang, $URI->uri_string);

                    $this->set_lang($this->default_lang);

                    /* redirect */
                    header('Location: '.$this->config['base_url'].$URI->uri_string.$this->query_string);

                    exit();
                }else{
                    $this->set_lang($this->uri_abbr);
                }
            }
        }

        log_message('debug', "Language_Identifier Class Initialized");
        
        /* control if lang is set in url and end slash is set */
        if(strlen($this->uri_abbr) === 2 AND empty($URI->segment(2)) AND substr($IN->server('REQUEST_URI', TRUE), -1) !== '/'){
            /* redirect */
            header('Location: '.$this->config['base_url'].$this->lang_abbr.$URI->uri_string.'/'.$this->query_string);
        }
    }

    /* set lang */
    private function set_lang($abbr)
    {
        global $IN;

        /* set cookie with lang */
        $IN->set_cookie('user_lang', $abbr, $this->config['lang_time']);

        /* set config language values to match the user language */
        $this->config['language'] = $this->lang_available[$abbr];
        $this->config['language_abbr'] = $abbr;
    }

    /* return currently lang set */
    public function lang()
    {
        return $this->config['language_abbr'];
    }

    /* return url for anchor and switch lang */
    public function switch_lang($lang, $uri = NULL)
    {
        global $URI;

        if($uri){
            $URI->uri_string = base_url() . $lang . '/' . $uri;
        }else{
            $URI->uri_string = preg_replace('/^\/?' . $this->uri_abbr . '/', $lang, $URI->uri_string);
        }

        return $URI->uri_string;
    }

    /* translate helper */
    public function t($line)
    {
        global $LANG;
        return ($t = $LANG->line($line)) ? $t : $line;
    }
    
    public function u($line)
    {
        global $LANG;
        return ($u = $LANG->line($line)) ? base_url() . $this->lang() . '/' . $u : $line;
    }

    public function u_list($name = NULL)
    {
        global $CFG;

        foreach($CFG->config['lang_available'] as $lang_abbr => $lang_name){
            require APPPATH . 'language/' . $lang_name . '/core/url_lang.php';

            $return[$lang_abbr] = (!empty($lang[$name])) ? $lang[$name] : NULL;
        }

        return $return;
    }
}
