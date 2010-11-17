<?php
/*******************************************************************************
VLLasku: web-based invoicing application.
Copyright (C) 2010 Ere Maijala

Portions based on:
PkLasku : web-based invoicing software.
Copyright (C) 2004-2008 Samu Reinikainen

This program is free software. See attached LICENSE.

*******************************************************************************/

/*******************************************************************************
VLLasku: web-pohjainen laskutusohjelma.
Copyright (C) 2010 Ere Maijala

Perustuu osittain sovellukseen:
PkLasku : web-pohjainen laskutusohjelmisto.
Copyright (C) 2004-2008 Samu Reinikainen

T�m� ohjelma on vapaa. Lue oheinen LICENSE.

*******************************************************************************/

//These are to prevent browser & proxy caching
 // HTTP/1.1
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
// HTTP/1.0
//header("Pragma: no-cache"); //when this commented mozilla doesn't cache images
// Date in the past
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// always modified
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
 
 
/********************************************************************
Includefile : htmlfuncs.php
    Functions to create various html-elements

Provides functions : 
    htmlPageStart( $strTitle )
    htmlListBox( $strName, $astrValues, $astrOptions, $strSelected )
    htmlFileListBox( $strName, $strDirName, 
                    $strSelected, $strExtension = "html" )
    
    htmlFormElement( $strName, $strType, $strValue, $strStyle, $strListQuery )
    
Includes files : -none-

Todo : add new functions...
********************************************************************/    

function htmlPageStart( $strTitle ) {
/********************************************************************
Function : htmlPageStart
    create Html-pagestart

Args : 
    $strTitle (string): pages title
    
Return : $strHtmlStart (string): page startpart

Todo : This could be more generic...
********************************************************************/

    $strHtmlStart = 
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">\n".
        "<html>\n".
        "<head>\n".
        "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-15\">\n\t".
        "<title>".$strTitle."</title>\n\t".
        "<meta name=\"Generator\" content=\"jEdit\">\n\t".
        "<meta name=\"Copyright\" content=\"Ere Maijala 2010\">\n\t".
        "<meta name=\"Copyright\" content=\"Samu Reinikainen 2004-2008\">\n\t".
        //"<meta http-equiv=\"Default-Style\" content=\"GreenForest\">\n\t".
        "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" title=\"DullGrey\">\n\t".
        //"<link rel=\"stylesheet\" type=\"text/css\" href=\"htmlarea.css\" title=\"HTMLArea\">\n\t".
        "<link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\">\n".
        "</head>\n";

    return $strHtmlStart;
}

function htmlFrameSetStart( $strTitle ) {
/********************************************************************
Function : htmlFrameSetStart
    create Html-framesetstart

Args : 
    $strTitle (string): pages title
    
Return : $strHtmlStart (string): page startpart

Todo : This could be more generic...
********************************************************************/

    $strHtmlStart = 
        "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\">\n".
        "<html>\n".
        "<head>\n".
        "<title>".$strTitle."</title>\n\t".
        "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-15\">\n\t".
        "<meta name=\"Generator\" content=\"jEdit\">\n\t".
        "<meta name=\"Copyright\" content=\"Ere Maijala 2010\">\n\t".
        "<meta name=\"Copyright\" content=\"Samu Reinikainen 2004-2008\">\n\t".
        //"<meta http-equiv=\"Default-Style\" content=\"GreenForest\">\n\t".
        "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\" title=\"DullGrey\">\n\t".
        "<link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\">\n".
        "</head>\n";

    return $strHtmlStart;
}


function htmlListBox( $strName, $astrValues, $astrOptions, $strSelected, $strStyle = "", $blnOnChange = FALSE, $blnShowEmpty = TRUE, $astrAdditionalAttributes = '') {
/********************************************************************
Function : htmlListBox
    Create Html-listbox

Args : 
    $strName (string): listbox name
    $astrValues (stringarray): listbox values
    $astrOptions (stringarray): listbox options
    $strSelected (string): selected value
    
Return : $strListBox (string) : listbox element

Todo : 
********************************************************************/
    if( $blnOnChange ) {
        $strOnChange = "onchange='this.form.submit();'";
    }
    $strListBox = 
        "<select class=\"".$strStyle."\" id=\"".$strName."\" name=\"".$strName."\" ". $strOnChange. " $astrAdditionalAttributes>\n";
    if( $blnShowEmpty ) {
        $strListBox .= "<option value=\"\" selected> - </option>\n";
    }
    
    for( $i = 0; $i < count($astrValues); $i++ ) {
        if( $strSelected == $astrValues[$i] ) {
            $strSelect = "selected";
            //echo "$strSelected == ". $astrValues[$i]. " <br>";
        }
        else {
            $strSelect = "";
        }
        $strListBox .= 
            "<option value=\"".$astrValues[$i]."\" $strSelect>".
            $astrOptions[$i]."</option>\n";
    }        
    $strListBox .= "</select>\n";

    return $strListBox;
}

function htmlLinkListBox( $strName, $astrLinks, $astrOptions, $strSelected, $strStyle = "", $blnShowEmpty = TRUE ) {
/********************************************************************
Function : htmlListBox
    Create Html-listbox

Args : 
    $strName (string): listbox name
    $astrValues (stringarray): listbox values
    $astrOptions (stringarray): listbox options
    $strSelected (string): selected value
    
Return : $strListBox (string) : listbox element

Todo : 
********************************************************************/
    $strOnChange = "onchange='newwin(this.options[this.selectedIndex].value);'";
    $strListBox = 
        "<select class=\"".$strStyle."\" id=\"".$strName."\" name=\"".$strName."\" ". $strOnChange. ">\n";
    if( $blnShowEmpty ) {
        $strListBox .= "<option value=\"\" selected> - </option>\n";
    }
    
    for( $i = 0; $i < count($astrLinks); $i++ ) {
        if( $strSelected == $astrLinks[$i] ) {
            $strSelect = "selected";
            //echo "$strSelected == ". $astrValues[$i]. " <br>";
        }
        else {
            $strSelect = "";
        }
        $strListBox .= 
            "<option value=\"".$astrLinks[$i]."\" $strSelect>".
            $astrOptions[$i]."</option>\n";
    }        
    $strListBox .= "</select>\n";

    return $strListBox;
}

function htmlFileListBox( $strName, $strDirName, $strSelected, $strExtension, $strStyle = "" ) {
/********************************************************************
Function : htmlFileListBox
    Create Html-listbox from files 
    with given extension from given directory

Args : 
    $strName (string): listbox name
    $strDirName (string): folder to traverse
    $strSelected (string): selected value
    $strExtension (string): file extension to search
    
Return : $strListBox (string) : 
            listbox element on success
            FALSE on error

Todo : could be array of extensions to search. Sorting? Errors.
********************************************************************/
    $astrFiles = array();
    if( substr($strDirName, -1) == "/" ) {
        $strDirName = substr($strDirName,0, -1);
    }
    if( is_dir( $strDirName ) ) {
        $dh = opendir($strDirName);

        while( ($file = readdir($dh)) !== FALSE ) {       
            if (eregi("\.". $strExtension ."$", $file)) {
                $astrFiles[] = $file;
            }
        }
        closedir($dh);
        sort($astrFiles);
        $strListBox = htmlListBox($strName, $astrFiles, $astrFiles, $strSelected, $strStyle);

        return $strListBox;
    }
    else {
        return FALSE;
    }
}

function getSQLResult( $strQuery ) {
/********************************************************************
Function : getSQLResult
    Return sql-query results

Args : 
    $strQuery (string): query to execute
        
Return : $strResult (string) : 
            result string on success
            FALSE on error

Todo : style, Sorting? Errors. Allow only select query?
********************************************************************/
    $intRes = mysql_query( $strQuery );
    if( $intRes ) {
        $strValue = mysql_result($intRes, 0, 0);
        return $strValue;
    }
    else {
        return FALSE;
    }
}
function htmlSQLListBox( $strName, $strQuery, $strSelected, $strStyle = "", $intOnChange = 0, $astrAdditionalAttributes ) {
/********************************************************************
Function : htmlSQLListBox
    Create Html-listbox from results of given query

Args : 
    $strName (string): listbox name
    $strQuery (string): query to execute
    $strSelected (string): selected value
    
Return : $strListBox (string) : 
            listbox element on success
            FALSE on error

Todo : style, Sorting? Errors. Allow only select query?
********************************************************************/
    $astrResults = array();
    //echo $strQuery;
    $intRes = mysql_query( $strQuery );
    if( $intRes ) {
        $intNRes = mysql_num_rows($intRes);
        for( $i = 0; $i < $intNRes; $i++ ) {
            $astrValues[$i] = mysql_result($intRes, $i, 0);
            $astrOptions[$i] = mysql_result($intRes, $i, 1);
        }
        $strListBox = htmlListBox($strName, $astrValues, $astrOptions, $strSelected, $strStyle, $intOnChange, TRUE, $astrAdditionalAttributes );

        return $strListBox;
    }
    else {
        return FALSE;
    }
}
function getSQLListBoxSelectedValue( $strQuery, $strSelected ) {
/********************************************************************
Function : htmlSQLListBox
    Create Html-listbox from results of given query

Args : 
    $strName (string): listbox name
    $strQuery (string): query to execute
    $strSelected (string): selected value
    
Return : $strListBox (string) : 
            listbox element on success
            FALSE on error

Todo : style, Sorting? Errors. Allow only select query?
********************************************************************/
    $astrResults = array();
    
    $intRes = mysql_query( $strQuery );
    if( $intRes ) {
        $intNRes = mysql_num_rows($intRes);
        for( $i = 0; $i < $intNRes; $i++ ) {
            $astrValues[$i] = mysql_result($intRes, $i, 0);
            $astrOptions[$i] = mysql_result($intRes, $i, 1);
            if( $astrValues[$i] == $strSelected ) {
                $strSelectedValue = $astrOptions[$i];
            }
        }
 
        return $strSelectedValue;
    }
    else {
        return FALSE;
    }
}

/********************************************************************
Function : htmlFormElement
    Create html formelements

Args : 
    $strName (string): element name
    $strType (string): element type
    $strValue (string): element value
    
Return : $strFormElement : html formelement

Todo : 
    Check values. Errors. Style?
********************************************************************/
function htmlFormElement( $strName, $strType, $strValue, $strStyle, $strListQuery, $strMode = "MODIFY", $strParentKey = NULL, $strTitle = "", $astrDefaults = array(), $astrAdditionalAttributes = '' ) {
    switch( $strType ) {
        case 'TEXT' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<input type=\"text\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\" $astrAdditionalAttributes>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = $strValue;
            }
            else {
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'PASSWD' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<input type=\"password\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"\" $astrAdditionalAttributes>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = "********\n";
            }
            else {
                $strFormElement = "********\n";
            }
        break;
        case 'CHECK' :
            if( $strMode == "MODIFY" ) {
                $strValue = $strValue ? 'checked' : '';
                $strFormElement = 
                "<input type=\"checkbox\" id=\"" . $strName . "\" name=\"". $strName. "\" value=\"1\" ". $strValue. " $astrAdditionalAttributes>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strValue = $strValue ? "X" : "";
                $strFormElement = $strValue;
            }
            else {
                $strValue = $strValue ? $GLOBALS['locYES'] : $GLOBALS['locNO'];
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'RADIO' :
            if( $strMode == "MODIFY" ) {
                $strChecked = $strValue ? 'checked' : '';
                $strFormElement = 
                "<input type=\"radio\" id=\"" . $strName . "\" name=\"". $strName. "\" value=\"".$strValue."\" $astrAdditionalAttributes>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strValue = $strValue ? "X" : "";
                $strFormElement = $strValue;
            }
            else {
                $strValue = $strValue ? $GLOBALS['locYES'] : $GLOBALS['locNO'];
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'INT' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<input type=\"text\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\" $astrAdditionalAttributes>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = $strValue;
            }
            else {
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'INTDATE' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<input type=\"text\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\" $astrAdditionalAttributes>\n";
                if( $strListQuery == "gif" ) {
                    $strExtension = "gif";
                }
                else {
                    $strExtension = "png";
                }
                $strFormElement .=
                    "<a class=\"tinyactionlink\" href=\"#\" onclick=\"OpenCalendar('". $strName. "', event); return false;\">". $GLOBALS['locCALENDAR']. "</a>";
                    
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = $strValue;
            }
            else {
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'TIMESTAMP' :
            $strFormElement = $strValue . "\n";
        break;
        case 'TIME' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<input type=\"text\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\" $astrAdditionalAttributes>\n";
                $strFormElement .=
                    "<a class=\"tinyactionlink\" href=\"#\" onclick=\"OpenClock('". $strName. "', event); return false;\">". $GLOBALS['locCLOCK']. "</a>";
                    
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = $strValue;
            }
            else {
                $strFormElement = $strValue . "\n";
            }
        break;
        case 'HID_TEXT' :
            $strFormElement = 
                "<input type=\"hidden\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'HID_INT' :
            $strFormElement = 
                "<input type=\"hidden\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'HID_INTDATE' :
            $strFormElement = 
                "<input type=\"hidden\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'HID_TIME' :
            $strFormElement = 
                "<input type=\"hidden\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'HID_AREA' :
            $strFormElement = 
                "<input type=\"hidden\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'HID_LIST' :
            $strFormElement = 
                "<input type=\"hidden\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" value=\"" . $strValue . "\">\n";
        break;
        case 'AREA' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = 
                "<textarea rows=\"24\" cols=\"80\" class=\"" . $strStyle . "\" ".
                "id=\"" . $strName . "\" name=\"" . $strName . "\" $astrAdditionalAttributes>" . $strValue . "</textarea>\n";
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = $strValue;
            }
            else {
                $strFormElement = nl2br($strValue) . "\n";
            }
            break;
        
        case 'RESULT' :
            $strListQuery = str_replace("_ID_", $strValue, $strListQuery);
            if( $strMode != "PDF" ) {
                $strFormElement = getSQLResult( $strListQuery ) . "\n";
            }
            else {
                $strFormElement = getSQLResult( $strListQuery );
            }
            
        break;
        case 'LIST' :
            if( $strMode == "MODIFY" ) {
            error_log("LIST: $astrAdditionalAttributes");
                $strFormElement = htmlSQLListBox( $strName, $strListQuery, $strValue, $strStyle, 0, $astrAdditionalAttributes );
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = getSQLListBoxSelectedValue( $strListQuery, $strValue );
            }
            else {
                $strFormElement = getSQLListBoxSelectedValue( $strListQuery, $strValue ) . "\n";
            }
        break;
        case 'SUBMITLIST' :
            if( $strMode == "MODIFY" ) {
                $strFormElement = htmlSQLListBox( $strName, $strListQuery, $strValue, $strStyle, 1, $astrAdditionalAttributes );
            }
            elseif( $strMode == "PDF" ) {
                $strFormElement = getSQLListBoxSelectedValue( $strListQuery, $strValue );
            }
            else {
                $strFormElement = getSQLListBoxSelectedValue( $strListQuery, $strValue ) . "\n";                 
            }
        break;
        
        case 'IFRAME' :
            if( $strValue ) {
                if( $strMode == "MODIFY" ) {
                    $strFormElement = 
                    "<iframe src=\"iframe.php?ses=".$GLOBALS['sesID']."&selectform=" . $strName . "&" . $strParentKey . "=" . $strValue . "\" ".
                    "class=\"" . $strStyle . "\" id=\"" . $strName . "\" name=\"" . $strName . "\" $astrAdditionalAttributes>\n".
                    "<h3>Please, Use a browser!</h3>\n".
                    "</iframe>\n";
                }
                elseif( $strMode == "PDF" ) {
                    $strFormElement = $strValue;
                }
                else {
                    $strFormElement = 
                    "<iframe src=\"iframe_print.php?ses=".$GLOBALS['sesID']."&selectform=" . $strName . "&" . $strParentKey . "=" . $strValue . "\" ".
                    "class=\"" . $strStyle . "\" id=\"" . $strName . "\" name=\"" . $strName . "\" $astrAdditionalAttributes>\n".
                    "<h3>Please, Use a browser!</h3>\n".
                    "</iframe>\n";
                }
            }
            else {
                $strFormElement = $GLOBALS['locSAVEFIRST'];
            }
        break;
        case 'IFORM' :
            if( $strValue ) {
                if( $strMode == "MODIFY" ) {
                    if( is_array($astrDefaults) ) {
                        $strDefaults = "defaults=";
                        while (list($key, $val) = each($astrDefaults)) {
                           $strDefaults .= urlencode("$key>$val+");
                        }
                    }
                    $strFormElement = 
                    "<iframe src=\"iform.php?ses=".$GLOBALS['sesID']."&selectform=" . $strName . "&" . $strParentKey . "=" . $strValue . "&". $strDefaults. "\" ".
                    "class=\"" . $strStyle . "\" id=\"" . $strName . "\" name=\"" . $strName . "\" $astrAdditionalAttributes>\n".
                    "<h3>Please, Use a browser!</h3>\n".
                    "</iframe>\n";
                }
                elseif( $strMode == "PDF" ) {
                    $strFormElement = print_iform($strValue, $strParentKey, $strName);
                    
                }
                else {
                    if( is_array($astrDefaults) ) {
                        $strDefaults = "defaults=";
                        while (list($key, $val) = each($astrDefaults)) {
                           $strDefaults .= urlencode("$key>$val+");
                        }
                    }
                    $strFormElement = 
                    "<iframe src=\"iform.php?ses=".$GLOBALS['sesID']."&selectform=" . $strName . "&" . $strParentKey . "=" . $strValue . "&". $strDefaults. "&mode=VIEW\" ".
                    "class=\"" . $strStyle . "\" id=\"" . $strName . "\" name=\"" . $strName . "\" $astrAdditionalAttributes>\n".
                    "<h3>Please, Use a browser!</h3>\n".
                    "</iframe>\n";
                }
            }
            else {
                $strFormElement = $GLOBALS['locSAVEFIRST'];
            }
        break;
        case 'BUTTON' :
            $strListQuery = str_replace("_ID_", $strValue, $strListQuery);
            switch( $strStyle ) {
                case 'tiny' :
                    $strHW = "height=1,width=1,";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'small' :
                    $strHW = "height=200,width=200,";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'medium' :
                    $strHW = "height=400,width=400,";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'large' :
                    $strHW = "height=600,width=600,";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'xlarge' :
                    $strHW = "height=800,width=650,";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'full' :
                    $strHW = "";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
                case 'pdf' :
                    $strListQuery = str_replace("'","",$strListQuery);
                    $strHref = $strListQuery;
                    $strOnClick = "";
                break;
                default :
                    $strHW = "";
                    $strHref = "#";
                    $strOnClick = "onclick=\"window.open(".
                    $strListQuery .",'". $strHW. "menubar=no,scrollbars=no,".
                    "status=no,toolbar=no'); return false;\"";
                break;
            }
            if( $strValue ) {
                
                $strFormElement = 
                    "<a class=\"formbuttonlink\" href=\"$strHref\" $strOnClick $astrAdditionalAttributes>". $strTitle. "</a>";
                    
            }
            else {
                $strFormElement = $GLOBALS['locSAVEFIRST'];
            }
        break;
        
    }

    return $strFormElement;
}
?>