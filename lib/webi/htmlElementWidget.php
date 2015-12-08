<?php
/**
 * HTML Elements Widget Functions
 * Library fungsi untuk pembuatan kode-kode elemen HTML
 * 
 * @package core
 * @copyright (c) 2006 Awakami Development Team
 * @link http://www.awakami.com
 * @author Agung Harry Purnama (agung.hp@awakami.com)
 * @todo dependencies and runlevels!
 *
 * -------------------------------------------------------------------------------------
 *
 * Library ini mendefinisikan fungsi-fungsi (library) untuk pembuatan elemen-elemen umum 
 * HTML yang sering digunakan dalam tampilan modul aplikasi, khususnya elemen input 
 * dalam suatu form.
 *
 * Tujuan pembuatan fungsi-fungsi ini adalah untuk memberikan kemudahan dan keseragaman 
 * kepada developer dalam penulisan kode elemen-elemen HTML.
 *
 *  Nama Fungsi	            Keterangan
 *      htmlFormOpen()	     : Fungsi untuk membuat tag <form ...>
 *      htmlFormClose()	     : Fungsi untuk membuat tag </form>
 *      htmlInputText()	     : Fungsi untuk membuat single line text-input
 *      htmlInputPassword()	 : Fungsi untuk membuat single line text-input untuk password
 *      htmlInputCheckbox()	 : Fungsi untuk membuat checkbox
 *      htmlInputRadio()	 : Fungsi untuk membuat radio button
 *      htmlInputSubmit()	 : Fungsi untuk membuat submit button
 *      htmlInputReset()	 : Fungsi untuk membuat reset button
 *      htmlInputFile()	     : Fungsi untuk membuat file-select input
 *      htmlInputHidden()	 : Fungsi untuk membuat hidden input
 *      htmlInputImage()	 : Fungsi untuk membuat graphical submit button
 *      htmlInputButton()	 : Fungsi untuk membuat push-button
 *      htmlSelectOption()	 : Fungsi untuk membuat drop-down-list-box
 *      htmlSelectOptgroup() : Fungsi untuk membuat drop-down-list-box yang dikelompokan dalam beberapa pilihan
 *      htmlTextarea()	     : Fungsi untuk membuat multi-line-text input
 *      htmlLabel()	         : Fungsi untuk membuat label input
 *      htmlFieldset()	     : Fungsi untuk membuat kotak untuk kumpulan beberapa elemen form input
 *      htmlLink()	         : Fungsi untuk membuat hyperlink
 *      htmlImage()	         : Fungsi untuk membuat tag <img ...>
 *      htmlTextBold()	     : Fungsi untuk bold-text
 *      htmlTextItalic()	 : Fungsi untuk italic-text
 *      htmlTextColor()	     : Fungsi untuk memberikan warna pada text
 */


/**
 * htmlFormOpen
 * Fungsi untuk membuat tag <form ...>
 *  
 * @access  public
 * @param   string action   : url form
 * @param   string method   : http method (POST|GET), default POST
 * @param   string id       : form id
 * @param   string events   : javascript access for events
 * @param   string enctype  : ContentType -- default : "application/x-www-form-urlencoded"
 * @param   string accept   : ContentTypes -- list type MIME untuk upload file
 * @return  string html
 */
function htmlFormOpen($action, 
                      $method = "post", 
                      $id = "", 
                      $events = "",
                      $enctype = "application/x-www-form-urlencoded", 
                      $accept = ""){
    if (empty($action)) return "";

    return "\n".'<form action="'.$action.'" 
                       method="'.$method.'" 
                       id="'.$id.'" 
                       enctype="'.$enctype.'" 
                       accept="'.$accept.'" '.$events.'>'."\n";
}

/**
 * htmlFormClose
 * Fungsi untuk membuat tag </form>
 * 
 * @access public
 * @param none
 * @return string html
 */
function htmlFormClose(){
    return "\n</form>\n";
}

/**
 * htmlInputText
 * Fungsi untuk membuat single line text-input
 * 
 * @access   public
 * @param    string name        : nama input
 * @param    string value       : nilai awal input
 * @param    int    size        : lebar karakter input
 * @param    int    maxlength   : maksimum jumlah karakter
 * @param    bool   readonly    : readonly (true | false), default : false
 * @param    string events      : javascript access for events
 * @return   string html
 */
function htmlInputText($name, 
                       $value = "", 
                       $size = 32, 
                       $maxlength = "", 
                       $readonly = false, 
                       $events = ""){
    if (empty($name)) return "";
    
    return '<input type="text" 
                   id="'.$name.'" 
                   name="'.$name.'" 
                   value="'.$value.'" 
                   size="'.$size.'" 
                   maxlength="'.$maxlength.'" '.(($readonly) ? 'readonly' : '').' '.$events.'>';
}

/**
 * htmlInputPassword
 * Fungsi untuk membuat single line text-input untuk password
 * 
 * @access  public
 * @param   string  name        : nama input
 * @param   string  value       : nilai awal input
 * @param   int     size        : lebar karakter input
 * @param   int     maxlength   : maksimum jumlah karakter
 * @param   bool    readonly    : readonly (true | false), default : false
 * @param   string  events      : javascript access for events
 * @return  string  html
 */
function htmlInputPassword($name, 
                           $value = "", 
                           $size = 32, 
                           $maxlength = "", 
                           $readonly = false, 
                           $events = ""){
    if (empty($name)) return "";
    
    return '<input type="password" 
                   id="'.$name.'" 
                   name="'.$name.'" 
                   value="'.$value.'" 
                   size="'.$size.'" 
                   maxlength="'.$maxlength.'" '.(($readonly) ? 'readonly' : '').' '.$events.'>';
}

/**
 * htmlInputCheckbox
 * Fungsi untuk membuat checkbox
 * 
 * @access  public
 * @param   string  name       : nama input
 * @param   string  value      : nilai input
 * @param   bool    checked    : true | false, default false
 * @param   bool    disabled   : true | false, default false
 * @param   string  events     : javascript access for events
 * @return  string  html
 */
function htmlInputCheckbox($name, 
                           $value = "", 
                           $checked = false, 
                           $disabled = false, 
                           $events = ""){
    if (empty($name)) return "";
    
    return '<input type="checkbox" 
                   id="'.$name.'" 
                   name="'.$name.'" 
                   value="'.$value.'" '.(($checked) ? 'checked' : '').' '.(($disabled) ? 'disabled' : '').' '.$events.'>';
}

/**
 * htmlInputRadio
 * Fungsi untuk membuat radio button	 
 * 
 * @access  public
 * @param   striNg  name     : nama input
 * @param   string  value    : nilai input
 * @param   bool    checked  : true | false, default false
 * @param   bool    disabled : true | false, default false
 * @param   string  id       : id input untuk referensi script
 * @param   string  events   : javascript access for events
 * @return  string  html
 */
function htmlInputRadio($name, 
                        $value = "", 
                        $checked = false, 
                        $disabled = false, 
                        $id = "",
                        $events = ""){
    if (empty($name)) return "";
    
    if (empty($id)) $id = $name;
    
    return '<input type="radio" 
                   id="'.$id.'" 
                   name="'.$name.'" 
                   value="'.$value.'" '.(($checked) ? 'checked' : '').' '.(($disabled) ? 'disabled' : '').' '.$events.'>';
}

/**
 * htmlInputSubmit
 * Fungsi untuk membuat submit button
 * 
 * @access  public
 * @param   string name   : nama input
 * @param   string value  : nilai label input
 * @param   string events : javascript access for events
 * @return  string html
 */
function htmlInputSubmit($name, $value, $events = ""){
    if (empty($name)) return "";
    return '<input type="submit" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$events.'/>';
}

/**
 * htmlInputReset
 * Fungsi untuk membuat reset button
 * 
 * @access  public
 * @param   string name   : nama input
 * @param   string value  : nilai label input
 * @param   string events : javascript access for events
 * @return  string html
 */
function htmlInputReset($name, $value, $events = ""){
    if (empty($name)) return "";
    return '<input type="reset" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$events.'/>';
}

/**
 * htmlInputFile
 * Fungsi untuk membuat file-select input
 * 
 * @access  public
 * @param   string  name        : nama input
 * @param   string  value       : nilai awal input
 * @param   int     size        : lebar karakter input
 * @param   int     maxlength   : maksimum jumlah karakter
 * @param   string  events      : javascript access for events
 * @return  string  html
 */
function htmlInputFile($name, 
                       $value = "", 
                       $size = 32, 
                       $maxlength = "", 
                       $events = ""){
    return '<input type="file" 
                   id="'.$name.'" 
                   name="'.$name.'" 
                   value="'.$value.'" 
                   size="'.$size.'" 
                   maxlength="'.$maxlength.'" '.$events.'>';
}

/**
 * htmlInputHidden
 * Fungsi untuk membuat hidden input
 * 
 * @access  public
 * @param   string name   : nama input
 * @param   string value  : nilai awal input
 * @param   string events : javascript access for events
 * @return  string html
 */
function htmlInputHidden($name, $value = "", $events = ""){
    if (empty($name)) return "";
    return '<input type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'" '.$events.'/>';
}	 

/**
 * htmlInputButton
 * Fungsi untuk membuat push-button
 * 
 * @access  public
 * @param   string name   : nama input
 * @param   string value  : nilai awal input
 * @param   string events : javascript access for events
 * @param   string src    : url image source
 * @param   string alt    : alternate text for image
 * @return  string html
 */
function htmlInputButton($name, $value = "", $events = "", $src = "", $alt = ""){
    if (empty($name)) return "";
    
    if (!empty($src)){
        $src = ' src ="'.$src.'" alt="'.$alt.'" ';
    }
    
    return '<input type="button" 
                   id="'.$name.'" 
                   name="'.$name.'" 
                   value="'.$value.'"'.$src.' '.$events.'/>';    
}

/**
 * htmlSelectOption
 * Fungsi untuk membuat drop-down-list-box
 * 
 * @access  public
 * @param   string  name     : nama input
 * @param   array   data     : list options, dengan format array sebagai berikut 
 *                             $data[0 ... n] = array('id'       => 'nilai_id_nya',
 *                                                    'name'     => 'nilai_yang_ditampilkan_pada_list_option',
 *                                                    'selected' => true_atau_false);
 * @param   string  selected : selected index value
 * @param   string  events   : javascript access for events
 * @return  string  html
 */
function htmlSelectOption($name, $data = array(), $selectedID = "", $events = ""){
    if (empty ($name)) return "";
    
    
    // Set up selected if required
    if (!empty($selectedID)) {
        for ($i=0; !empty($data[$i]); $i++) {
            if ($data[$i]['id'] == $selectedID) {
                $data[$i]['selected'] = 1;
                break;
            }
        }
    }
    
    $html = '<select'
                .' name="'.$name.'"'
                .' id="'.$name.'"'
                .' '.$events.'>';
    foreach ($data as $datum)
    {
        $html .= '<option'
                    .' value="'.$datum['id'].'"'
                    .((empty ($datum['selected'])) ? '' : ' selected="selected"')
                    .'>'
                    .$datum['name']
                .'</option>';
    }
    $html .= '</select>';
    
    return $html;
}

/**
 * htmlSelectOptgroup
 * Fungsi untuk membuat drop-down-list-box yang dikelompokan dalam beberapa pilihan
 * 
 * @access  public
 * @param   string name       : nama input
 * @param   array   data      : list options, dengan format array sebagai berikut 
 *      $data[0 ... n] = array('id'       => 'nilai_id_nya',
 *                             'name'     => 'nilai_yang_ditampilkan_pada_list_option',
 *                             'gtype'    => true_atau_false,
 *                             'items'    => array(array('id'        => 'nilai_id_nya',                           
 *                                                        'name'     => 'nilai_yang_ditampilkan_pada_list_option',
 *                                                        'selected' => true_atau_false),
 *                                                  array(... dan sterusnya ...)));
 *       Keterangan :
 *       - Jika gtype == true, nilai items adalah sub-list dari group
 *       - Jika gtype == false, maka nilai items akan di-ignore (hint : set 'items' => array())
 *                                                                                                        
 * @param   string selectedID : selected index value
 * @param   string events     : javascript access for events
 * @return  string html
 */
function htmlSelectOptgroup($name, $data = array(), $selectedID = "", $events = ""){
    if (empty ($name)) return "";
    
    $html = '<select'
                .' name="'.$name.'"'
                .' id="'.$name.'"'
                .' '.$events.'>';
    foreach ($data as $datum){
        if ($datum['gtype'] == true){
            $html .= '<OPTGROUP label="'.$datum['name'].'">';
            foreach ($datum['items'] as $item){

                if ($item['id'] == $selectedID) $item['selected'] = 1;
                    
                $html .= '<option'
                            .' value="'.$item['id'].'"'
                            .((empty ($item['selected'])) ? '' : ' selected="selected"').'>'
                            .$item['name']
                        .'</option>';            
            }
            $html .= '</OPTGROUP>';
        }else{
            $html .= '<option'
                        .' value="'.$datum['id'].'"'
                        .((empty ($datum['selected'])) ? '' : ' selected="selected"')
                        .'>'
                        .$datum['name']
                    .'</option>';
        }
    }
    $html .= '</select>';
    
    return $html;    
}
     
/**
 * htmlTextarea
 * Fungsi untuk membuat multi-line-text input 
 * 
 * @access  public
 * @param   string name     : nama input
 * @param   string value    : nilai awal input
 * @param   int    cols     : jumlah kolom
 * @param   int    rows     : jumlah baris
 * @param   string events   : javascript access for events
 * @return  string html
 */
function htmlTextarea($name, $value = "", $cols = 60, $rows = 3, $events = ""){
    if (empty ($name)) return "";

    $html = '<textarea'
           .' name="'.$name.'"'
           .' id="'.$name.'"'
           .' rows="'.$rows.'"'
           .' cols="'.$cols.'" '.$events.'>'.$value.'</textarea>';
    return $html;
}

/**
 * htmlLabel
 * Fungsi untuk membuat label input
 * 
 * @access  public
 * @param   string label    : nilai label
 * @param   string for      : id atau nama label form input
 * @param   string title    : tool tips
 * @param   string id       : reference id for script
 * @param   string events   : javascript access for events
 * @return  string html
 */
function htmlLabel($label, $for = "", $title = "", $id = "", $events = ""){
    if (empty($label)) return "";
    
    return '<label for="'.$for.'" id="'.$id.'" title="'.$title.'" '.$events.'>'.$label.'</label>';
}

/**
 * htmlFieldsetOpen
 * Fungsi untuk membuat tag <fieldset ...> <legend> ...
 * 
 * @access public
 * @param  string legend   : string legend
 * @param  string id       : reference id for script
 * @param  string events   : javascript access for events
 * @return string html
 */
function htmlFieldsetOpen($legend = "", $id = "", $events = ""){
    return '<fieldset id="'.$id.'" '.$events.'><legend>'.$legend.'</legend>';
}	     

/**
 * htmlFieldsetClose
 * Fungsi untuk membuat tag </fieldset>
 * 
 * @access public
 * @param  none
 * @return string html
 */
function htmlFieldsetClose(){
    return "</fieldset>";
}

/**
 * htmlLink
 * Fungsi untuk membuat hyperlink
 * 
 * @access public
 * @param  string url    : url link
 * @param  string label  : label
 * @param  string id     : link id for script reference
 * @param  string events : javascript access for events
 * @return string html
 */
function htmlLink($url, $label, $title = "", $id = "", $events = ""){
    if (empty($url) || empty($label)) return "";
    
    return '<a href="'.$url.'" title="'.$title.'" id="'.$id.'" '.$events.'>'.$label.'</a>';
}

/**
 * htmlImage
 * Fungsi untuk membuat <img ...>
 * 
 * @access public
 * @param  string src    : url img source
 * @param  string alt    : alternate text
 * @param  int    border : img border
 * @param  int    width  : img width
 * @param  int    height : img height
 * @param  string id     : link id for script reference
 * @param  string events : javascript access for events
 * @return string html
 */
function htmlImage($src, 
                   $alt = "", 
                   $border = 0, 
                   $width = "", 
                   $height = "", 
                   $id = "", 
                   $events = ""){

    if (empty($src)) return "";
    
    $html = '<img src="'.$src.'" 
                  alt="'.$alt.'" 
                  border="'.$border.'" 
                  width="'.$width.'"
                  height="'.$height.'"
                  id="'.$id.'" '.$events.' />';
    return $html;
}

/**
 * htmlTextBold
 * Fungsi untuk bold-text   
 * 
 * @access public
 * @param  string text   : the text
 * @return string html
 */     
function htmlTextBold($text){
    if (empty($text)) return "";
    
    return '<strong>'.$text.'</strong>';
}

/**
 * htmlTextItalic
 * Fungsi untuk italic-text   
 * 
 * @access public
 * @param  string text   : the text
 * @return string html
 */     
function htmlTextItalic($text){
    if (empty($text)) return "";
    
    return '<em>'.$text.'</em>';
}

/**
 * htmlTextColor
 * Fungsi untuk memberikan warna pada text
 * 
 * @access public
 * @param  string text   : the text
 * @param  string color  : color in hexa decimal format
 * @return string html
 */     
function htmlTextColor($text, $color){
    if (empty($text)) return "";
    
    return '<font color="'.$color.'">'.$text.'</font>';
}

function initCalendar()
    {
        $output = <<<HEREDOC
    <style type="text/css">@import url("includes/jscalendar/calendar.css");</style>
    <script src="includes/jscalendar/calendar.js" type="text/javascript"></script>
    <script src="includes/jscalendar/lang/calendar-en.js" type="text/javascript"></script>
    <script src="includes/jscalendar/calendar-setup.js" type="text/javascript"></script>
HEREDOC;
        return $output;
    }
    
    /**
     * Add jscalendar text box (jscalendar 0.9.6)
     *
     * @param string $id the id of the input text
     * @param string $button the id of the trigger button
     * @param string $default the default value
     * @param string $str the button text
     * @return string $output
     * @author ahp
     */
    function htmlCalendarBox($id = 'tanggal', $button = 'trigger', $default = '', $str = '...', $act=''){
        $output = '';
	    $output .= "\t<input type=\"text\" id=\"$id\" name=\"$id\" \"$act\" value=\"$default\" size=\"10\"/>\n";
	    $output .= "\t<button id=\"$button\">$str</button>\n";
	    $output .= "\t<script type=\"text/javascript\">\n";
	    $output .= "\t\tCalendar.setup({inputField: \"$id\", ifFormat: \"%d-%m-%Y\", button: \"$button\"});\n";
	    $output .= "\t</script>\n";
	    return $output;
    }         
    function htmlUploadFile($fieldname, $size=32, $maxsize=1000000, $accesskey='')
    {
        if (empty ($fieldname))
        {
            return;
        }
        
        $output = '<input type="hidden" name="MAX_FILE_SIZE" value="'.xarVarPrepForDisplay($maxsize).'" />';
        $output .= '<input'
            .' type="file"'
            .' name="'.xarVarPrepForDisplay($fieldname).'"'
            .' id="'.xarVarPrepForDisplay($fieldname).'"'
            .' size="'.xarVarPrepForDisplay($size).'"'
            .((empty ($accesskey)) ? '' : ' accesskey="'.xarVarPrepForDisplay($accesskey).'"')
            .' />';
            return $output;
    }
	
	/**
 * htmlMonthOption
 * Fungsi untuk membuat drop-down nama-nama bulan
 * 
 * @access public
 * @param  string id   		: bulan
 * @param  string $default  : kemunculan pilihan default
 * @return string html
 */    
    function htmlMonthOption($id = 'bulan', $default=''){
		$output ="";
		$output .= "<select name=\"$id\">";
		$output .= '<option value=""'.(($default!='')? '' :'selected="selected"').'>-- Pilih --';
		$output .= '<option value="1"'.(($default!=1)? '' :'selected="selected"').'>Januari';
		$output .= '<option value="2"'.(($default!=2)? '' :'selected="selected"').'>Februari';
		$output .= '<option value="3"'.(($default!=3)? '' :'selected="selected"').'>Maret';
		$output .= '<option value="4"'.(($default!=4)? '' :'selected="selected"').'>April';
		$output .= '<option value="5"'.(($default!=5)? '' :'selected="selected"').'>Mei';
		$output .= '<option value="6"'.(($default!=6)? '' :'selected="selected"').'>Juni';
		$output .= '<option value="7"'.(($default!=7)? '' :'selected="selected"').'>Juli';
		$output .= '<option value="8"'.(($default!=8)? '' :'selected="selected"').'>Agustus';
		$output .= '<option value="9"'.(($default!=9)? '' :'selected="selected"').'>September';
		$output .= '<option value="10"'.(($default!=10)? '' :'selected="selected"').'>Oktober';
		$output .= '<option value="11"'.(($default!=11)? '' :'selected="selected"').'>November';
		$output .= '<option value="12"'.(($default!=12)? '' :'selected="selected"').'>Desember';
		$output .="</select>";
		return $output;
	}
	
    function htmlPager($startNum, $total, $urltemplate, $itemsPerPage = 10, $blockOptions = array(), $template = 'default')
    {
        // Quick check to ensure that we have work to do
        if ($total <= $itemsPerPage) {return '';}

        // Number of pages in a page block - support older numeric 'pages per block'.
        if (is_numeric($blockOptions)) {
            $blockOptions = array('blocksize' => $blockOptions);
        }

        // Pass the url template into the pager calculator.
        $blockOptions['urltemplate'] = $urltemplate;

        // Get the pager information.
        $data = htmlPagerInfo($startNum, $total, $itemsPerPage, $blockOptions);

        // Nothing to do: perhaps there is an error in the parameters?
        if (empty($data)) {return '';}

        /* ---- */
        $_first_1 = 'arrow-le-1.gif';
        $_first_2 = 'arrow-le-2.gif';
        $_prev_1  = 'arrow-l-1.gif';
        $_prev_2  = 'arrow-l-2.gif';
        $_next_1  = 'arrow-r-1.gif';
        $_next_2  = 'arrow-r-2.gif';
        $_last_1  = 'arrow-re-1.gif';
        $_last_2  = 'arrow-re-2.gif';
        $_fnext_1 = 'arrow-rr-1.gif';
        $_fnext_2 = 'arrow-rr-2.gif';
        $_fprev_1 = 'arrow-ll-1.gif';
        $_fprev_2 = 'arrow-ll-2.gif';

        $output = '<div align="left"><table border="0" cellpadding="2">'."\n".
                  '<tr>'."\n".'<td>';

        if ($data['totalpages'] > 1){
            if ($data['currentpagenum'] != $data['firstpagenum']){
                $output .= '<a title="Halaman pertama" href="'.$data['firsturl'].'"><img src="images/pager/'.$_first_1.'" alt="|;&lt;"></a>';
            }else{
                $output .= '<span title="Halaman pertama"><img src="images/pager/'.$_first_2.'" alt="|;&lt;"></span>';
            }
        }

        $output .= '</td><td>';

        if ($data['totalblocks'] > 1){
            $prevblocklabel = "Sebelumnya ".$data['prevblockpages']." halaman";
            if ($data['currentblock'] != $data['firstblock']){
                $output .= '<a title="'.$prevblocklabel.'" href="'.$data['prevblockurl'].'"><img src="images/pager/'.$_fprev_1.'" alt="&lt;&lt;"></a>';
            }else{
                $output .= '<span title="'.$prevblocklabel.'"><img src="images/pager/'.$_fprev_2.'" alt="&lt;&lt;"></span>';
            }
        }
        $output .= '</td><td>';


        if ($data['totalblocks'] == 1){
            $prevpagelabel = 'Halaman sebelumnya; '.$data['prevpageitems'].' data';
            if ($data['prevpageitems'] > 0){
                $output .= '<a title="'.$prevpagelabel.'" href="'.$data['prevpageurl'].'"><img src="images/pager/'.$_prev_1.'" alt="&lt;&lt;"></a>';
            }else{
                $output .= '<span title="'.$prevpagelabel.'"><img src="images/pager/'.$_prev_2.'" alt="&lt;&lt;"></span>';
            }
        }

        $output .= '</td><td>';

        foreach ($data['middleurls'] as $pagenumber => $pageurl){
            $output .= '<font style="font-size: 12px;"><strong>';
            if ($pagenumber != $data['currentpage']){
                if ($data['middleitemsfrom'][$pagenumber] != $data['middleitemsto'][$pagenumber]){
                    $pageurllabel = 'Halaman '.$pagenumber.' (data ke '.$data['middleitemsfrom'][$pagenumber].' sampai '.$data['middleitemsto'][$pagenumber].')';
                }else{
                    $pageurllabel = 'Halaman '.$pagenumber.'';
                }
                $output .= '&nbsp;<a href="'.$pageurl.'" title="'.$pageurllabel.'">'.$pagenumber.'</a>';
            }else{
                $pageurllabel = 'Halaman '.$pagenumber.'';
                $output .= '&nbsp;<span title="'.$pageurllabel.'">'.$pagenumber.'</span>';
            }
            $output .= '</strong></font>';
        }

        $output .= '</td><td>';

        if ($data['totalblocks'] == 1){
            $nextpagelabel = 'Halaman selanjutnya; '.$data['nextpageitems'].' data';
            if ($data['nextpageitems'] > 0){
                $output .= '<a title="'.$nextpagelabel.'" href="'.$data['nextpageurl'].'"><img src="images/pager/'.$_next_1.'" alt="&gt;"></a>';
            }else{
                $output .= '<span title="'.$nextpagelabel.'"><img src="images/pager/'.$_next_2.'" alt="&gt;"></span>';
            }
        }

        $output .= '</td><td>';


        if ($data['totalblocks'] > 1){
            $nextblocklabel ='Selanjutnya '.$data['nextblockpages'].' halaman';
            if ($data['currentblock'] != $lastblock){
                $output .= '<a title="'.$nextblocklabel.'" href="'.$data['nextblockurl'].'"><img src="images/pager/'.$_fnext_1.'" alt="&gt;&gt;"></a>';
            }else{
                $output .= '<span title="'.$nextblocklabel.'"><img src="images/pager/'.$_fnext_2.'" alt="&gt;&gt;"></span>';
            }
        }

        $output .= '</td><td>';

        if ($data['totalpages'] > 1){
            if ($data['currentpagenum'] != $data['lastpagenum']){
                $output .= '<a title="Halaman terakhir" href="'.$data['lasturl'].'"><img src="images/pager/'.$_last_1.'" alt="&gt;|"></a>';
            }else{
                $output .= '<span title="Halaman terakhir"><img src="images/pager/'.$_last_2.'" alt="&gt;|"></span>';
            }
        }

        $output .= '</td></tr><tr><td colspan="7" align="center">';
        $output .= '[ '.$data['totalpages'].' Halaman dengan '.$data['totalitems'].' Data ]';
        $output .= '</td></tr></table></div>';

        return $output;
    }

    function htmlPagerInfo($currentItem, $total, $itemsPerPage = 10, $blockOptions = 10)
    {
        // Default block options.
        if (is_numeric($blockOptions)) {
            $pageBlockSize = $blockOptions;
        }

        if (is_array($blockOptions)) {
            if (!empty($blockOptions['blocksize'])) {$blockSize = $blockOptions['blocksize'];}
            if (!empty($blockOptions['firstitem'])) {$firstItem = $blockOptions['firstitem'];}
            if (!empty($blockOptions['firstpage'])) {$firstPage = $blockOptions['firstpage'];}
            if (!empty($blockOptions['urltemplate'])) {$urltemplate = $blockOptions['urltemplate'];}
            if (!empty($blockOptions['urlitemmatch'])) {
                $urlItemMatch = $blockOptions['urlitemmatch'];
            } else {
                $urlItemMatch = '%%';
            }
            $urlItemMatchEnc = rawurlencode($urlItemMatch);
        }

        // Default values.
        if (empty($blockSize) || $blockSize < 1) {$blockSize = 10;}
        if (empty($firstItem)) {$firstItem = 0;}
        if (empty($firstPage)) {$firstPage = 1;}

        // The last item may be offset if the first item is not 1.
        $lastItem = ($total + $firstItem - 1);

        // Sanity check on arguments.
        if ($itemsPerPage < 1) {$itemsPerPage = 10;}
        if ($currentItem < $firstItem) {$currentItem = $firstItem;}
        if ($currentItem > $lastItem) {$currentItem = $lastItem;}

        
        // Max number of items in a block of pages.
        $itemsPerBlock = ($blockSize * $itemsPerPage);

        // Get the start and end items of the page block containing the current item.
        $blockFirstItem = $currentItem - (($currentItem - $firstItem) % $itemsPerBlock);
        $blockLastItem = $blockFirstItem + $itemsPerBlock - 1;
        if ($blockLastItem > $lastItem) {$blockLastItem = $lastItem;}

        // Current/Last page numbers.
        $currentPage = (int)ceil(($currentItem-$firstItem+1) / $itemsPerPage) + $firstPage - 1;
        $totalPages = (int)ceil($total / $itemsPerPage);

        // First/Current/Last block numbers
        $firstBlock = 1;
        $currentBlock = (int)ceil(($currentItem-$firstItem+1) / $itemsPerBlock);
        $totalBlocks = (int)ceil($total / $itemsPerBlock);

        // Get start and end items of the current page.
        $pageFirstItem = $currentItem - (($currentItem-$firstItem) % $itemsPerPage);
        $pageLastItem = $pageFirstItem + $itemsPerPage - 1;
        if ($pageLastItem > $lastItem) {$pageLastItem = $lastItem;}

        // Initialise data array.
        $data = array();

        $data['middleitems'] = array();
        $pageNum = (int)ceil(($blockFirstItem - $firstItem + 1) / $itemsPerPage) + $firstPage - 1;
        for ($i = $blockFirstItem; $i <= $blockLastItem; $i += $itemsPerPage) {
            if (!empty($urltemplate)) {
                $data['middleurls'][$pageNum] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $i, $urltemplate);
            }
            $data['middleitems'][$pageNum] = $i;
            $data['middleitemsfrom'][$pageNum] = $i;
            $data['middleitemsto'][$pageNum] = $i + $itemsPerPage - 1;
            if ($data['middleitemsto'][$pageNum] > $total) {$data['middleitemsto'][$pageNum] = $total;}
            $pageNum += 1;
        }

        $data['currentitem'] = $currentItem;
        $data['totalitems'] = $total;
        $data['lastitem'] = $lastItem;
        $data['firstitem'] = $firstItem;
        $data['itemsperpage'] = $itemsPerPage;
        $data['itemsperblock'] = $itemsPerBlock;
        $data['pagesperblock'] = $blockSize;

        $data['currentblock'] = $currentBlock;
        $data['totalblocks'] = $totalBlocks;
        $data['firstblock'] = $firstBlock;
        $data['lastblock'] = $totalBlocks;
        $data['blockfirstitem'] = $blockFirstItem;
        $data['blocklastitem'] = $blockLastItem;

        $data['currentpage'] = $currentPage;
        $data['currentpagenum'] = $currentPage;
        $data['totalpages'] = $totalPages;
        $data['pagefirstitem'] = $pageFirstItem;
        $data['pagelastitem'] = $pageLastItem;

        // These two are item numbers. The naming is historical.
        $data['firstpage'] = $firstItem;
        $data['lastpage'] = $lastItem - (($lastItem-$firstItem) % $itemsPerPage);

        if (!empty($urltemplate)) {
            // These two links are for first and last pages.
            $data['firsturl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['firstpage'], $urltemplate);
            $data['lasturl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['lastpage'], $urltemplate);
        }

        $data['firstpagenum'] = $firstPage;
        $data['lastpagenum'] = ($totalPages + $firstPage - 1);

        // Data for previous page of items.
        if ($currentPage > $firstPage) {
            $data['prevpageitems'] = $itemsPerPage;
            $data['prevpage'] = ($pageFirstItem - $itemsPerPage);
            if (!empty($urltemplate)) {
                $data['prevpageurl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['prevpage'], $urltemplate);
            }
        } else {
            $data['prevpageitems'] = 0;
        }

        // Data for next page of items.
        if ($pageLastItem < $lastItem) {
            $nextPageLastItem = ($pageLastItem + $itemsPerPage);
            if ($nextPageLastItem > $lastItem) {$nextPageLastItem = $lastItem;}
            $data['nextpageitems'] = ($nextPageLastItem - $pageLastItem);
            $data['nextpage'] = ($pageLastItem + 1);
            if (!empty($urltemplate)) {
                $data['nextpageurl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['nextpage'], $urltemplate);
            }
        } else {
            $data['nextpageitems'] = 0;
        }

        // Data for previous block of pages.
        if ($currentBlock > $firstBlock) {
            $data['prevblockpages'] = $blockSize;
            $data['prevblock'] = ($blockFirstItem - $itemsPerBlock);
            if (!empty($urltemplate)) {
                $data['prevblockurl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['prevblock'], $urltemplate);
            }
        } else {
            $data['prevblockpages'] = 0;
        }

        // Data for next block of pages.
        if ($currentBlock < $totalBlocks) {
            $nextBlockLastItem = ($blockLastItem + $itemsPerBlock);
            if ($nextBlockLastItem > $lastItem) {$nextBlockLastItem = $lastItem;}
            $data['nextblockpages'] = ceil(($nextBlockLastItem - $blockLastItem) / $itemsPerPage);
            $data['nextblock'] = ($blockLastItem + 1);
            if (!empty($urltemplate)) {
                $data['nextblockurl'] = str_replace(array($urlItemMatch,$urlItemMatchEnc), $data['nextblock'], $urltemplate);
            }
        } else {
            $data['nextblockpages'] = 0;
        }

        return $data;
    }

    function htmlMessage($message, $type = 'error'){
        if (empty($message)) return '';
        
        return '<div class="'.$type.'msg">'.$message.'</div>';
    }

?>