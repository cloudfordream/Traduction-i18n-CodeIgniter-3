# Traduction-i18n-CodeIgniter-3

Class for i18n traduction with CodeIgniter 3

With this class, you will be able to translate your site with CodeIgniter 3 in all the languages you want, you will also be able to add more quickly when you want.

The system applies the default language of CodeIgniter if the visitor does not yet have a cookie, then the cookie is given priority.

If an unsupported language is typed in the url by the visitor he will be automatically redirected to the default language set in CodeIgniter.

If the visitor arrives on the site with a languageless url (/fr/ or /en/ etc) he will be redirected in the language of his cookie or the default language of CodeIgniter if no cookie is present.

# How to setup

- Take the contents of the files present in */application/config* and add it to your configuration files.
- Take the following files and import them into your FTP:
  - *application/controllers/Welcome.php*
  - *application/core/MY_Lang.php*
  - *application/language/english/welcome_lang.php*
  - *application/language/french/welcome_lang.php*
  
# Future improvement

- Support for visitor's browser language.
