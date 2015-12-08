<?php
class wbPage extends Object
{
    private static $theme = '';
    private static $page = '';

    private static $title = '';
    
    private static $metaCollection;
    private static $styleCollection;
    private static $scriptCollection;
    
    private static $content = '';
    
    public static function init(){
        self::setTheme(wbConfig::get('Theme.defaultTheme'));
        self::setPage(wbConfig::get('Theme.defaultPage'));
        self::setTitle(wbConfig::get('Theme.siteTitle'));
    }
    
    public static function getThemeName(){
        return self::$theme;
    }
    
    public static function render($content = '')
    {
        // override content
        if (!empty($content)) self::$content = $content;
        
        echo wbTemplate::getPageOutput(
                self::$theme,
                self::$page, 
                array(
                    'wbPageTitle' => self::$title,
                    'wbPageMeta' => self::getMetaCollection(),
                    'wbPageStyle' => self::getStyleCollection(),
                    'wbPageScriptHead' => self::getScriptCollection('head'),
                    'wbPageScriptBody' => self::getScriptCollection('body'),
                    'wbPageContent' => self::$content,
                    'wbPageThemeDir' => wbTemplate::$theme_dir.'/'.self::$theme
                )
             );
    }

    public static function showExceptionPage($message = "", $title = ""){
        if (empty($title)) $title = "Pesan Kesalahan";

        if (empty($message)) $message = "Terjadi kesalahan pada halaman yang anda request, mohon periksa juga penulisan URL anda";

        wbTemplate::setModTemplate('base', 'base-exception');

        $data = array('title' => $title,
                      'message' => $message);

        return $data;
    }

    public static function show404Page(){
        return self::showExceptionPage('<h3>Halaman Tidak Ditemukan</h3>Halaman yang anda cari mungkin sudah dihapus atau dipindahkan. Pastikan juga penulisan URL anda sudah benar', 'Kesalahan 404');
    }

    public function setTheme($name)
    {
        if (is_dir(wbTemplate::$theme_dir.'/'.$name)) self::$theme = $name;
    }
    
    public function setPage($page)
    {
        if (is_file(wbTemplate::$theme_dir.'/'.self::$theme.'/'.wbTemplate::$themepages_dir.'/'.$page.'.php')) self::$page = $page;
    }
    
    public static function setTitle($title)
    {
        self::$title = $title;
    }
    
    public static function addMeta($properties)
    {
        if (empty($properties)) return;

        if (!is_array(self::$metaCollection)) self::$metaCollection = array();

        self::$metaCollection[] = $properties;
    }

    public static function addStyle($href, $media = 'screen', $rel = 'stylesheet', $type = 'text/css')
    {
        if (empty($href) || empty($media) || empty($rel) || empty($type)) return;
        
        if (!is_array(self::$styleCollection)) self::$styleCollection = array();
        
        self::$styleCollection[] = array('href' => $href, 'media' => $media, 'rel' => $rel, 'type' => $type);        
    }

    public static function addScript($src, $pos = 'head', $type = 'text/javascript', $if = '')
    {
        if (empty($src) || empty($pos) || empty($type)) return;
        
        if (!is_array(self::$scriptCollection)) self::$scriptCollection = array();
        
        if (!isset(self::$scriptCollection[$pos])) self::$scriptCollection[$pos] = array();
        
        self::$scriptCollection[$pos][] = array('src' => $src, 'type' => $type, 'if' => $if);
    }

    public static function addScriptCode($code, $pos = 'head', $type = 'text/javascript')
    {
        if (empty($code) || empty($pos) || empty($type)) return;
        
        if (!is_array(self::$scriptCollection)) self::$scriptCollection = array();
        
        if (!isset(self::$scriptCollection[$pos])) self::$scriptCollection[$pos] = array();
        
        self::$scriptCollection[$pos][] = array('code' => $code, 'type' => $type);
    }

    
    public static function getMetaCollection($string = true){
        if (!$string) return self::$metaCollection;
        
        $output = '';
        for($i=0; $i < count(self::$metaCollection); $i++){
            $output .= '<meta '.self::$metaCollection[$i].' />'."\n";
        }
        return $output;
    }

    public static function getStyleCollection($string = true){
        if (!$string) return self::$styleCollection;
        
        $output = '';
        for($i=0; $i < count(self::$styleCollection); $i++){
            $output .= '<link rel="'.self::$styleCollection[$i]['rel'].'"'
                           .' type="'.self::$styleCollection[$i]['type'].'"'
                           .' href="'.self::$styleCollection[$i]['href'].'"'
                           .' media="'.self::$styleCollection[$i]['media'].'" />'."\n";
        }
        return $output;
    }

    public static function getScriptCollection($pos, $string = true){
        if (!$string) return self::$scriptCollection;
        
        $output = '';
        // check for $pos index
        if (!isset(self::$scriptCollection[$pos])) return $output;
        
        for($i=0; $i < count(self::$scriptCollection[$pos]); $i++){
            if (!empty(self::$scriptCollection[$pos][$i]['src'])){

                $if_start = $if_end = '';
                if (!empty(self::$scriptCollection[$pos][$i]['if'])){
                    $if_start = '<!--[if '.self::$scriptCollection[$pos][$i]['if'].']>';
                    $if_end = '<![endif]-->';
                }

                $output .= $if_start.'<script type="'.self::$scriptCollection[$pos][$i]['type'].'"'
                             .' src="'.self::$scriptCollection[$pos][$i]['src'].'"></script>'.$if_end."\n";
            }else if (!empty(self::$scriptCollection[$pos][$i]['code'])){
                $output .= '<script type="'.self::$scriptCollection[$pos][$i]['type'].'">'.self::$scriptCollection[$pos][$i]['code'].'</script>'."\n";
            }
        }
        return $output;
    }

    public static function setContent($content){
        self::$content = $content;
    }
    
    public static function addContent($content){
        self::$content .= $content;
    }    
}

class wbTemplate extends Object
{
    // modules directory
    public static $mod_dir = 'modules';
    // modules templates directory
    public static $modviews_dir = 'views';
    
    public static $mod_template = '';

    // themes directory
    public static $theme_dir = 'themes';
    // themes pages directory
    public static $themepages_dir = 'pages';

    public static function setModTemplate($module, $template){
        self::$mod_template = self::$mod_dir.'/'.$module.'/'.self::$modviews_dir.'/'.$template.'.php';
    }

    public static function &getModView(&$data)
    {
        $output = self::getOutput(self::$mod_template, $data);
        return $output;
    }

    public static function getPageOutput($theme, $page, $data)
    {
        return self::getOutput(self::$theme_dir.'/'.$theme.'/'.self::$themepages_dir.'/'.$page.'.php', $data);
    }

    public static function getOutput($tplFile, &$data = array())
    {
        if (!is_file($tplFile)) throw new Exception('Template '.$tplFile.' is not a file or not exist');

        ob_start();
        extract($data);
        include $tplFile;
        $output = ob_get_clean();
        return $output;
    }
}
