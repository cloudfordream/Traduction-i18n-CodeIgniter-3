<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Lang extends CI_Lang
{

    private $config;
    
    private $index_page;
    private $default_abbr;
    private $lang_available;
    private $lang_uri_ignore;

    private $uri_abbr;

    public function __construct()
    {

        parent::__construct();
        
        global $URI, $CFG, $IN;
        
        $this->config =& $CFG->config;
        
        $this->index_page      = $this->config['index_page'];
        $this->default_abbr    = $this->config['language_abbr'];
        $this->lang_available  = $this->config['lang_available'];
        $this->lang_uri_ignore = $this->config['lang_uri_ignore'];

        $this->uri_abbr = $URI->segment(1);

        /* contril if lang is set in url and end slash is set */
        if(strlen($this->uri_abbr) === 2 AND empty($URI->segment(2)) AND substr($IN->server('REQUEST_URI'), -1) !== '/'){

            header('Location: '.$this->config['base_url'].$this->index_page.$URI->uri_string.'/');

        }

        /* verify if uri is in lang_uri_ignore */
        if(!in_array($this->uri_abbr, $this->lang_uri_ignore, TRUE)){

            /* adjust the uri string leading slash */
            $URI->uri_string = preg_replace("|^\/?|", '/', $URI->uri_string);

            /* control if lang cookie exist */
            if(!is_null($IN->cookie('user_lang')) AND !isset($this->uri_abbr)){

                /* set lang in uri */    
                $this->index_page .= empty($this->index_page) ? $IN->cookie('user_lang', TRUE) : "/".$IN->cookie('user_lang', TRUE);
                
                if(strlen($this->uri_abbr) === 2){

                    /* remove invalid abbreviation */
                    $URI->uri_string = preg_replace("|^\/?$this->uri_abbr|", '', $URI->uri_string);

                }
                
                /* redirect */
                header('Location: '.$this->config['base_url'].$this->index_page.$URI->uri_string.'');

                exit();

                /* set the language_abbreviation cookie */                
                $IN->set_cookie('user_lang', $this->default_abbr, $this->config['lang_time']);

            }else{

                /* check validity against config array */
                if(isset($this->lang_available[$this->uri_abbr])){
            
                    /* reset uri segments and uri string */
                    $URI->segment(array_shift($URI->segments));
                    $URI->uri_string = preg_replace("|^\/?$this->uri_abbr|", '', $URI->uri_string);
                    
                    /* set config language values to match the user language */
                    $this->config['language'] = $this->lang_available[$this->uri_abbr];
                    $this->config['language_abbr'] = $this->uri_abbr;
                            
                    /* check and set the uri identifier */
                    $this->index_page .= empty($this->index_page) ? $this->uri_abbr : "/$this->uri_abbr";
        
                    /* set the language abbreviation cookie */               
                    $IN->set_cookie('user_lang', $this->uri_abbr, $this->config['lang_time']);
                
                } else { 

                    /* check if cookie exist */
                    (!empty($IN->cookie('user_lang', TRUE))) ? $bypass_abbr = $IN->cookie('user_lang', TRUE) : $bypass_abbr = $this->default_abbr;

                    /* check and set the uri identifier to the default value */
                    $this->index_page .= empty($this->index_page) ? $bypass_abbr : "/$bypass_abbr";
                    
                    if(strlen($this->uri_abbr) === 2){

                        /* remove invalid abbreviation */
                        $URI->uri_string = preg_replace("|^\/?$this->uri_abbr|", '', $URI->uri_string).'';

                    }
                    
                    /* redirect */
                    (!empty($URI->uri_string)) ? header('Location: '.$this->config['base_url'].$this->index_page.$URI->uri_string) : header('Location: '.$this->config['base_url'].$this->index_page.'/'.$URI->uri_string);
                    
                    exit();
    
                    /* set the language abbreviation cookie */                
                    $IN->set_cookie('user_lang', $this->default_abbr, $this->config['lang_time']);
                    
                }
                
                log_message('debug', "Language_Identifier Class Initialized");
            }
        }
    }

    /* return currently lang set */
    public function lang()
    {
        return $this->config['language_abbr'];
    }

    /* return url for anchor and switch lang */
    public function switch_lang($lang)
    {
        global $URI;

        (substr($URI->uri_string, 0, 1) !== '/') ? $slash = '/' : $slash = NULL;

        return $lang.$slash.$URI->uri_string;
    }

    /* translate helper */
    public function t($line)
    {
        global $LANG;
        return ($t = $LANG->line($line)) ? $t : $line;
    }
}
