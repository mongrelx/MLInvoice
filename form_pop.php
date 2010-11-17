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

require "htmlfuncs.php";
require "sqlfuncs.php";
require "sessionfuncs.php";
require "miscfuncs.php";
require "datefuncs.php";

$strSesID = $_REQUEST['ses'] ? $_REQUEST['ses'] : FALSE;

if( !sesCheckSession( $strSesID ) ) {
    die;
}
require "localize.php";

$strForm = $_POST['selectform'] ? $_POST['selectform'] : $_REQUEST['selectform'];
$strMode = $_GET['actmode'] ? $_GET['actmode'] : 'MODIFY';
$strMode = $_POST['actmode'] ? $_POST['actmode'] : $strMode;

require "form_switch.php";

echo htmlPageStart( _PAGE_TITLE_ );


//print_r($_POST);
$blnNew = (int)$_POST['newact'] || (int)$_REQUEST['new'] ? TRUE : FALSE;
$blnCopy = (int)$_POST['copyact'] ? TRUE : FALSE;
$blnSave = (int)$_POST['saveact'] ? TRUE : FALSE;
$blnDelete = (int)$_POST['deleteact'] ? TRUE : FALSE;
$intKeyValue = (int)$_POST[$strPrimaryKey] ? (int)$_POST[$strPrimaryKey] : (int)$_REQUEST[$strPrimaryKey];



//if NEW is clicked clear existing form data
if( $blnNew && !$blnSave ) {
    unset($intKeyValue);
    unset($astrValues);
    unset($_POST);
    
}

//initialize elements
for( $i = 0; $i < count($astrFormElements); $i++ ) {
    if( $astrFormElements[$i]['type'] == 'IFRAME' || $astrFormElements[$i]['type'] == 'IFORM' || $astrFormElements[$i]['type'] == 'BUTTON' || $astrFormElements[$i]['type'] == 'RESULT' ) {
        $astrValues[$astrFormElements[$i]['name']] = $intKeyValue ? $intKeyValue : FALSE;
    }
    else {
         if( !$astrFormElements[$i]['default'] ) {
            $astrValues[$astrFormElements[$i]['name']] =             $_POST[$astrFormElements[$i]['name']] ? $_POST[$astrFormElements[$i]['name']] : FALSE;
            $astrValues[$astrFormElements[$i]['name']] =             $astrValues[$astrFormElements[$i]['name']] ? $astrValues[$astrFormElements[$i]['name']] : $_GET[$astrFormElements[$i]['name']];
            
            if( $astrFormElements[$i]['default'] == "DATE_NOW" ) {
               $strDefaultValue = date("d.m.Y");
            }
            elseif( strstr($astrFormElements[$i]['default'], "DATE_NOW+") ) {
                $atmpValues = explode("+", $astrFormElements[$i]['default']);
               $strDefaultValue = date("d.m.Y",mktime(0, 0, 0, date("m"), date("d")+$atmpValues[1], date("Y")));
            }            
            elseif( $astrFormElements[$i]['default'] == "TIME_NOW" ) {
               $strDefaultValue = date("H:i");
            }
            elseif( $astrFormElements[$i]['default'] == "TIMESTAMP_NOW" ) {
               $strDefaultValue = date("d.m.Y H:i");
            }
            else {
                $strDefaultValue = $astrFormElements[$i]['default'];
            }
            $astrValues[$astrFormElements[$i]['name']] =             $_POST[$astrFormElements[$i]['name']] ? $_POST[$astrFormElements[$i]['name']] : $_GET[$astrFormElements[$i]['name']];
            $astrValues[$astrFormElements[$i]['name']] =             $astrValues[$astrFormElements[$i]['name']] ? $astrValues[$astrFormElements[$i]['name']] : $strDefaultValue;
        
    }
}
}
//save the form values when user hits SAVE
if( $blnSave ) { 
    //check all form elements which save values
    for( $i = 0; $i < count($astrFormElements); $i++ ) {
        //lets shorten our if's and get array variables to tmp vars
        $strControlType = $astrFormElements[$i]['type'];
        $strControlName = $astrFormElements[$i]['name'];
        $mixControlValue = $astrValues[$strControlName];
                
        //don't handle IFRAME, IFORM, BUTTON, LABEL elements
        if( $strControlType != 'IFRAME' && $strControlType != 'IFORM' && $strControlType != 'BUTTON' && $strControlType != 'LABEL' ) {
            //if element hasn't value and null's aren't allowed raise error
            if( $strControlType == "INT" ) {
                if ( !isset($mixControlValue) && !$astrFormElements[$i]['allow_null'] ) {
                    $blnMissingValues = TRUE;
                    $strOnLoad .= "alert('".$GLOBALS['locERRVALUEMISSING']." : ".$astrFormElements[$i]['label']."');";
                }
            }
            else {
                if ( !$mixControlValue && !$astrFormElements[$i]['allow_null'] ) {
                    $blnMissingValues = TRUE;
                    $strOnLoad .= "alert('".$GLOBALS['locERRVALUEMISSING']." : ".$astrFormElements[$i]['label']."');";
                }
            }
        }
    }
    //if no required values missing -> create the sql-query fields 
    if( !$blnMissingValues ) {
        for( $i = 0; $i < count($astrFormElements); $i++ ) {
            $strControlType = str_replace("HID_", "", $astrFormElements[$i]['type']);
            $strControlName = $astrFormElements[$i]['name'];
            $mixControlValue = $astrValues[$strControlName];
            //elements with text or varchar datatype need ' '
            if( $strControlType == 'TEXT' || $strControlType == 'AREA' || $strControlType == 'PASSWD' ) {
                //build the insert into fieldnames
                $strFields .= $strControlName. ", ";
                //build the insert into fieldvalues
                $strInsert .= "'". gpcAddSlashes($mixControlValue). "', ";
                //build the update fields & values
                $strUpdateFields .= 
                  $strControlName. "='". gpcAddSlashes($mixControlValue). "', ";
            }
            //elements that are numeric TODO: do we need to save 0(zero)
            elseif( $strControlType == 'INT' || $strControlType == 'LIST' ) {
                //build the insert into fields
                $strFields .= $strControlName. ", ";
                //format the numbers to right format - finnish use ,-separator
                $flttmpValue = 
                    $mixControlValue ? str_replace(",", ".", $mixControlValue) : 0;
                if( !is_numeric($mixControlValue) ) {
                        //build the insert into fieldvalues
                        $strInsert .= "'". $flttmpValue. "', ";
                        //build the update fields & values
                        $strUpdateFields .= 
                            $strControlName. "='". $flttmpValue. "', ";
                    }
                    else {
                        //build the insert into fieldvalues
                        $strInsert .= (float)$flttmpValue. ", ";
                        //build the update fields & values
                        $strUpdateFields .= 
                            $strControlName. "=". (float)$flttmpValue. ", ";
                    }
                
            }
            //checkboxelements handled bit differently than other int's
            elseif( $strControlType == 'CHECK' ) {
                //build the insert into fields
                $strFields .= $strControlName. ", ";
                //if checkbox checked save 1 else 0 TODO: consider TRUE/FALSE
                $tmpValue = $mixControlValue ? 1 : 0;
                //build the insert into fieldvalues
                $strInsert .= $tmpValue.", ";
                //build the update fields & values
                $strUpdateFields .= $strControlName. "=". $tmpValue. ", ";
            }
            //date-elements need own formatting too
            elseif( $strControlType == 'INTDATE' ) {
                if( !$mixControlValue ) {
                    $mixControlValue = 'NULL';
                }
                //build the insert into fields
                $strFields .= $strControlName. ", ";
                //build the insert into fieldvalues
                //convert user input to right format
                $strInsert .= 
                    dateConvDate2IntDate($mixControlValue). ", ";
                //build the update fields & values
                //convert user input to right format
                $strUpdateFields .= 
                    $strControlName. "=". dateConvDate2IntDate($mixControlValue). ", ";
            }
            elseif( $strControlType == 'TIMESTAMP' ) {
                /*if( !$mixControlValue ) {
                    $mixControlValue = 'NULL';
                }*/
                //build the insert into fields
                $strFields .= $strControlName. ", ";
                //build the insert into fieldvalues
                //convert user input to right format
                $strInsert .= time(). ", ";
                //build the update fields & values
                //convert user input to right format
                /*$strUpdateFields .= 
                    $strControlName. "=". dateConvDate2IntDate($mixControlValue). ", ";*/
            }
            //time-elements need own formatting too
            elseif( $strControlType == 'TIME' ) {
                $astrSearch = array('.', ',', ' ');
                //build the insert into fields
                $strFields .= $strControlName. ", ";
                //build the insert into fieldvalues
                //convert user input to right format
                
                $strInsert .= "'". str_replace($astrSearch, ":", $mixControlValue). "', ";
                //build the update fields & values
                //convert user input to right format
                $strUpdateFields .= 
                    $strControlName. "='". str_replace($astrSearch, ":", $mixControlValue). "', ";
            }
            
        }
    }
    //if no required values missing -> create the final sql-query 
    if( !$blnMissingValues ) {
        //substract last loops unnecessary ', '-parts 
        $strInsert = substr($strInsert, 0, -2);
        $strFields = substr($strFields, 0, -2);
        $strUpdateFields = substr($strUpdateFields, 0, -2);
        //if we are inserting brandnew entry into database
        if( $blnNew ) {
            //create "insert into"-query with fields created abowe
            $strQuery =
                "INSERT INTO " . $strTable . " ( ".
                $strFields . " ) ".
                "VALUES ( ".
                $strInsert . " );";
        }
        //if we are updating existing data in database
        else {
            //create "update"-query with fields created abowe
            $strQuery =
                "UPDATE " . $strTable . " SET ".
                $strUpdateFields . " ".
                "WHERE ". $strPrimaryKey . "=" . $intKeyValue . "";
        
        }
        //echo $strQuery."<br>\n";
        
        
        $intRes = @mysql_query($strQuery);
        
        if( $intRes ) {
            //if we added new entry to database we have to get it's ID
            if( $blnNew ) {
                //get the latest insert ID from mysql
                $intKeyValue = mysql_insert_id();
                //$intKeyValue = mysql_result( $intRes, 0, $strPrimaryKey );
                
            }
            //TODO : think this list update system...
            //$strOnLoad = "window.open('list.php?ses=". $GLOBALS['sesID']. "&form=" . $strForm . "','f_list');";
            
            //insert is now done - set the new flag to FALSE
            //then the next query will be update
            $blnNew = FALSE;
            //insert went fine - let the user know it
            $blnInsertDone = TRUE;
            //$strOnLoad = "top.frset_bottom.f_list.document.forms[0].key_values.value=''; top.frset_bottom.f_list.document.forms[0].submit();";
        }
        //if there's no resource identifier something went wrong
        else {
            //let the user know that query didn't workout
            //on stable end-user version only possible reason for this to
            //happen is when there are unique-fields in table and
            //user is trying to save duplicate data
            $strOnLoad = "alert('".$GLOBALS['locERRDUPLUNIQUE']."');";
        }
    }
}

//did the user press delete-button
//if we have primarykey we can fulfill his commands
if( $blnDelete && $intKeyValue ) {
    //create the delete query
    $strQuery =
        "DELETE FROM " . $strTable . " ".
        "WHERE " . $strPrimaryKey . "=" . $intKeyValue . ";";
    //send query to database
    $intRes = @mysql_query($strQuery);
    //if delete was succesfull we have res-id
    if( $intRes ) {
        //dispose the primarykey value
        unset($intKeyValue);
        //clear form elements
        unset($astrValues);
        $blnNew = TRUE;
        //$strOnLoad = "top.frset_bottom.f_list.document.forms[0].key_values.value=''; top.frset_bottom.f_list.document.forms[0].submit();";
        //$strOnLoad = "window.open('list.php?ses=". $GLOBALS['sesID']."&form=" . $strForm . "','f_list');";
    }
    //if delete-query didn't workout
    else {
        //tell user what happened
        //only possible reason for delete to fail is
        //when table has references to other tables
        //with mysql - I don't know why I even bother...
        $strOnLoad = "alert('".$GLOBALS['locERRDELREFERENCE']."');";
    }
}

if( $intKeyValue ) {
    $strQuery =
        "SELECT * FROM " . $strTable . " ".
        "WHERE " . $strPrimaryKey . "=" . $intKeyValue . ";";
    $intRes = mysql_query($strQuery);
    $intNRows = mysql_numrows($intRes);
    if( $intNRows ) {
        for( $j = 0; $j < count($astrFormElements); $j++ ) {
            $strControlType = $astrFormElements[$j]['type'];
            $strControlName = $astrFormElements[$j]['name'];
            
            if( $strControlType == 'IFRAME' || $strControlType == 'IFORM' || $strControlType == 'RESULT' ) {
               $astrValues[$strControlName] = $intKeyValue;
            }
            elseif( $strControlType == 'BUTTON' ) {
                if( strstr($astrFormElements[$j]['listquery'], "=_ID_") ) {
                    $astrValues[$strControlName] = $intKeyValue ? $intKeyValue : FALSE;
                }
                else {
                    $tmpListQuery = $astrFormElements[$j]['listquery'];
                    $strReplName = substr($tmpListQuery, strpos($tmpListQuery, "_"));
                    $strReplName = strtolower(substr($strReplName, 1, strrpos($strReplName, "_")-1));
                    $astrValues[$strControlName] = $astrValues[$strReplName];
                    //echo "$strControlName $strReplName". $astrValues[$strReplName];
                    $astrFormElements[$j]['listquery'] = str_replace(strtoupper($strReplName), "ID", $astrFormElements[$j]['listquery']);
                }
            }
            elseif( $strControlType != 'LABEL' ) {
                if( $strControlType == 'INTDATE' ) {
                    $astrValues[$strControlName] =                         dateConvIntDate2Date( mysql_result( $intRes, 0, $strControlName ));
                }
                elseif( $strControlType == 'TIMESTAMP' ) {
                        $astrValues[$strControlName] =                         date("d.m.Y H:i", mysql_result( $intRes, $i, $strControlName ));
                }
                
                else { 
                    $astrValues[$strControlName] = 
                    mysql_result($intRes, 0, $strControlName);
                }
            }
        }
    }
    else {
        echo $GLOBALS['locENTRYDELETED']; die;
    }
}

?>
<body class="form" onload="<?php echo $strOnLoad?>">


<script type="text/javascript">
<!--
function OpenCalendar(datefield, event) {
    x = event.screenX;
    y = event.screenY;
    strLink = 'calendar.php?ses=<?php echo $GLOBALS['sesID']?>&datefield=' + datefield;
    
    window.open(strLink, 'calendar', 'height=260,width=280,screenX=' + x + ',screenY=' + y + ',left=' + x + ',top=' + y + ',menubar=no,scrollbars=yes,status=no,toolbar=no');
    
    return true;
}
function OpenClock(timefield, event) {
    x = event.screenX;
    y = event.screenY;
    strLink = 'clock.php?ses=<?php echo $GLOBALS['sesID']?>&timefield=' + timefield;
    
    window.open(strLink, 'clock', 'height=150,width=200,screenX=' + x + ',screenY=' + y + ',left=' + x + ',top=' + y + ',menubar=no,scrollbars=yes,status=no,toolbar=no');
    
    return true;
}
function OpenUploader(valuefield, imagepath, check, event) {
    x = event.screenX;
    y = event.screenY;
    strLink = 'uploadpicture.ph?ses=<?php echo $GLOBALS['sesID']?>&valuefield=' + valuefield + '&imgpath=' + imagepath + '&check=' + check; 
    
    window.open(strLink, 'uploader', 'height=400,width=400,screenX=' + x + ',screenY=' + y + ',left=' + x + ',top=' + y + ',menubar=no,scrollbars=yes,status=no,toolbar=no');
    
    return true;
}

-->
</script>

<form method="post" action="form_pop.php?selectform=<?php echo $strForm?>&ses=<?php echo $GLOBALS['sesID']?>" target="_self" name="admin_form">
<?php

?> 
<input type="hidden" name="<?php echo $strPrimaryKey?>" value="<?php echo $intKeyValue?>">
<input type="hidden" name="mode" value="<?php echo $strMode?>">
<table>
<?php
for( $j = 0; $j < count($astrFormElements); $j++ ) {
    if($astrFormElements[$j]['type'] == "LABEL") {
?>
    <tr>
        <td class="sublabel" colspan="4">
            <?php echo $astrFormElements[$j]['label']?> 
        </td>
    </tr>
<?php
    }
    else {
        if( $astrFormElements[$j]['position'] == 0 && $astrFormElements[$j]['type'] != "HID_INT" && $astrFormElements[$j]['type'] != "HID_TEXT" && !strstr($astrFormElements[$j]['type'], "HID_") ) {
            echo "\t<tr>\n";
            $strColspan = "colspan=\"3\"";
            $intColspan = 4;
        }
        elseif( $astrFormElements[$j]['position'] == 1 && $astrFormElements[$j]['type'] != "HID_INT" && $astrFormElements[$j]['type'] != "HID_TEXT" && !strstr($astrFormElements[$j]['type'], "HID_") ) {
            echo "\t<tr>\n";
            $strColspan = "colspan=\"0\"";
            $intColspan = 2;
        }
        else {
            $intColspan = 2;
        }
        
        if( $blnNew && ( $astrFormElements[$j]['type'] == "BUTTON" || $astrFormElements[$j]['type'] == "IFORM" || $astrFormElements[$j]['type'] == "IFRAME" ) ) {
            echo "<td class=\"label\" colspan=\"2\">&nbsp;</td>";
        }
        elseif( $astrFormElements[$j]['type'] == "IFORM" || $astrFormElements[$j]['type'] == "IFRAME" ) {
 ?>
        <td class="label" colspan="<?php echo $intColspan?>">
            <?php echo $astrFormElements[$j]['label']?> :
            <br>
            <?php echo htmlFormElement($astrFormElements[$j]['name'], $astrFormElements[$j]['type'],                               gpcStripSlashes($astrValues[$astrFormElements[$j]['name']]),                               $astrFormElements[$j]['style'],$astrFormElements[$j]['listquery'], $strMode, $astrFormElements[$j]['parent_key'])?>
        </td>
<?php          
        }
        elseif( $astrFormElements[$j]['type'] == "BUTTON" ) {
 ?>
        <td class="button" colspan="<?php echo $intColspan?>">
            <?php echo htmlFormElement($astrFormElements[$j]['name'], $astrFormElements[$j]['type'],                               gpcStripSlashes($astrValues[$astrFormElements[$j]['name']]),                               $astrFormElements[$j]['style'],$astrFormElements[$j]['listquery'], $strMode, $astrFormElements[$j]['parent_key'],$astrFormElements[$j]['label'])?>
        </td>
<?php          
        }
        
        elseif( $astrFormElements[$j]['type'] == "HID_INT" || $astrFormElements[$j]['type'] == "HID_TEXT" || strstr($astrFormElements[$j]['type'], "HID_") ) {
 ?>
        <?php echo htmlFormElement($astrFormElements[$j]['name'], $astrFormElements[$j]['type'],                               gpcStripSlashes($astrValues[$astrFormElements[$j]['name']]),                               $astrFormElements[$j]['style'],$astrFormElements[$j]['listquery'], "MODIFY", $astrFormElements[$j]['parent_key'],$astrFormElements[$j]['label'])?>
<?php          
        }
        else {
?>
        <td class="label">
            <?php echo $astrFormElements[$j]['label']?> :
        </td>
        <td class="field" <?php echo $strColspan?>>
            <?php echo htmlFormElement($astrFormElements[$j]['name'], $astrFormElements[$j]['type'],                               gpcStripSlashes($astrValues[$astrFormElements[$j]['name']]),                               $astrFormElements[$j]['style'],$astrFormElements[$j]['listquery'], $strMode, $astrFormElements[$j]['parent_key'])?>
        </td>
<?php
        }
        
        if( $astrFormElements[$j]['position'] == 0 || $astrFormElements[$j]['position'] == 2 ) {
            echo "\t</tr>\n";
        }
    }
}
if( $blnNew ) {
    $intNew = 1;
}
else {
    $intNew = 0;
}
?>
</table>
<input type="hidden" name="saveact" value="0">
<input type="hidden" name="newact" value="<?php echo $intNew?>">
<input type="hidden" name="copyact" value="0">
<input type="hidden" name="deleteact" value="0">
<table>
<?php
if( $strMode == "MODIFY" ) {
?>
<tr>
    <td>
        <a class="actionlink" href="#" onclick="self.document.forms[0].saveact.value=1; self.document.forms[0].submit(); return false;"><?php echo $GLOBALS['locSAVE']?></a>
    </td>
<?php
/*if( !$blnNew ) {
?>    
    <!--<td>
        <input type="image" name="copy" src="./<?php echo $GLOBALS['sesLANG']?>_images/copy.gif" title="Copy current values">
    </td> wait for next version-->
    <td>
        <a class="actionlink" href="#" onclick="self.document.forms[0].newact.value=1; self.document.forms[0].submit(); return false;"><?php echo $GLOBALS['locNEW']?></a>
    </td>
    <td>
        <a class="actionlink" href="#" onclick="if(confirm('<?php echo $GLOBALS['locCONFIRMDELETE']?>')==true) {  self.document.forms[0].deleteact.value=1; self.document.forms[0].submit(); return false;} else{ return false; }"><?php echo $GLOBALS['locDELETE']?></a>        
    </td>
<?php
}*/
}
?>
<td>
    <a class="actionlink" href="#" onclick="opener.document.forms[0].submit(); self.close();"><?php echo $GLOBALS['locCLOSE']?></a>
</td>
</tr>        
</table>
</form>
</body>
</html>