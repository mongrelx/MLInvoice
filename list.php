<?php
/*******************************************************************************
PkLasku : web-based invoicing software.
Copyright (C) 2004-2008 Samu Reinikainen

This program is free software. See attached LICENSE.

*******************************************************************************/

/*******************************************************************************
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

echo htmlPageStart( _PAGE_TITLE_ );

$strForm = $_POST['selectform'] ? $_POST['selectform'] : $_REQUEST['selectform'];

$strWhereClause = $_POST['where'] ? $_POST['where'] : $_REQUEST['where'];
$intCategoryID = $_POST['category_id'] ? $_POST['category_id'] : $_REQUEST['category_id'];
$strSearchTerms = trim($_POST['searchterms']) ? trim($_POST['searchterms']) : FALSE;
$astrKeyValues = $_POST['key_values'] ? explode(";",$_POST['key_values']) : NULL;
$intPage = (int)$_POST['page'] ? (int)$_POST['page'] : 1;
$blnPrevious = (int)$_POST['prev'] ? TRUE : FALSE;
$blnNext = (int)$_POST['forw'] ? TRUE : FALSE;
$intID = $_REQUEST['id'] ? $_REQUEST['id'] : FALSE;



if( $blnPrevious ) {
    $intPage -= 1;
}
if( $blnNext ) {
    $intPage += 1;
}

require "list_switch.php";

if( $intCategoryID ) {
    $strHiddenTerm = "<input type=\"hidden\" name=\"category_id\" value=\"". $intCategoryID."\">";
    $strHiddenWhere = 
        " AND ". $astrHiddenSearchField['name']. "=". $intCategoryID. " ";
}

if( !$astrKeyValues ) {
    if( $strWhereClause ) {
        $strWhereClause = "WHERE " . gpcStripSlashes(urldecode($strWhereClause));
        $strWhereClause = str_replace("%-", "%", $strWhereClause);
    }
    elseif( $strSearchTerms == "*"  && !$intID ) {
        $strWhereClause = "WHERE " . $strPrimaryKey . " IS NOT NULL ";
    }
    elseif( !$strSearchTerms && !$intID ) {
        $strWhereClause = "WHERE " . $strPrimaryKey . " IS NOT NULL ";
        $strOrderClause2 = " " . $strPrimaryKey . " DESC ";
    }
    else {
        $astrTerms = explode(" ",$strSearchTerms);
        $strWhereClause = "WHERE ";
        for( $i = 0; $i < count($astrTerms); $i++ ) {
            if( $astrTerms[$i] || $intID ) {
                $strWhereClause .= "(";
                for( $j = 0; $j < count($astrSearchFields); $j++ ) {
                    if( $astrSearchFields[$j]['type'] == "TEXT" ) {
                        $strWhereClause .= $astrSearchFields[$j]['name'] . " LIKE '%" . $astrTerms[$i] . "%' OR ";
                    }
                    elseif( $astrSearchFields[$j]['type'] == "INT" && preg_match ("/^([0-9]+)$/", $astrTerms[$i]) ) {
                        $strWhereClause .= $astrSearchFields[$j]['name'] . " = " . (int)$astrTerms[$i] . " OR ";
                    }
                    elseif( $astrSearchFields[$j]['type'] == "PRIMARY" && preg_match ("/^([0-9]+)$/", $intID) ) {
                        $strWhereClause = 
                            "WHERE ". $astrSearchFields[$j]['name']. " = ". (int)$intID. "     ";
                        unset($astrSearchFields);
                        break 2;
                    }
                    
                }
                $strWhereClause = substr( $strWhereClause, 0, -3) . ") AND ";
            }
        }
        $strWhereClause = substr( $strWhereClause, 0, -4);
    }
    if( $strOrderClause2 ) {
        $strOrderClause = $strOrderClause2;
    }
    else {
        for( $j = 0; $j < count($astrShowFields); $j++ ) {
            $strOrderClause .= $strTable. ".". $astrShowFields[$j]['name'] . " ASC, ";
        }
        $strOrderClause = substr( $strOrderClause, 0, -2);
    }
    $strQuery = 
        "SELECT $strTable." . $strPrimaryKey . " FROM " . $strTable . " " .
        $strInnerJoin. $strWhereClause. $strHiddenWhere. $strAddWhere. 
        " ORDER BY " . $strOrderClause . ";";

    $intRes = mysql_query($strQuery);
    if( $intRes ) {
        $intTotal = mysql_num_rows($intRes);
        for( $i = 0; $i < $intTotal; $i++ ) {
            $astrKeyValues[$i] = mysql_result($intRes, $i, $strPrimaryKey);
        }
    }
}
//echo $strQuery;

if( count($astrKeyValues) > 0 ) {
    $intTotal = count($astrKeyValues);
    $intLimit = $leftNaviListRows; //how many results to show on page
    if( $intTotal > $intLimit ) {
        $intEnd = $intLimit * $intPage;
        if( $intEnd > $intTotal ) {
            $intEnd = $intTotal;
            $intStart = $intEnd - ($intLimit - ($intLimit * $intPage - $intTotal));
        }
        else {
            $intStart = $intEnd - $intLimit;
        }
        
    }
    else {
        $intEnd = $intTotal;
        $intStart = 0;
    }
    //echo $intStart . " - " . $intEnd . " / " . $intTotal;
    for( $i = $intStart; $i < $intEnd; $i++ ) {
        $strKeysIn .= $astrKeyValues[$i].",";
    }
    $strKeysIn = substr($strKeysIn, 0, -1);
    $strSelectClause = $strPrimaryKey .",";
    $strOrderClause = "";
    for( $j = 0; $j < count($astrShowFields); $j++ ) {
        $strOrder = $astrShowFields[$j]['order'] ? $astrShowFields[$j]['order'] : "ASC";
        $strSelectClause .= $astrShowFields[$j]['name'] . ",";
        $strOrderClause .= $astrShowFields[$j]['name'] . " $strOrder, ";
    }
    $strSelectClause = substr($strSelectClause, 0, -1);
    if( $strOrderClause2 ) {
        $strOrderClause = $strOrderClause2;
    }
    else {
        $strOrderClause = substr($strOrderClause, 0, -2);
    }
    $strQuery =
        "SELECT " . $strSelectClause . " FROM " . $strTable . " ".
        "WHERE " . $strPrimaryKey . " IN (" . $strKeysIn . ") ".
        "ORDER BY " . $strOrderClause . ";";
    //echo $strQuery;
    $intRes = mysql_query($strQuery);
    if( $intRes ) {
        $intNRes = mysql_num_rows($intRes);
        for( $i = 0; $i < $intNRes; $i++ ) {
            $astrPrimaryKeys[$i] = mysql_result($intRes, $i, $strPrimaryKey);
            for( $j = 0; $j < count($astrShowFields); $j++ ) {
                //$astrListValues[$i] .= mysql_result($intRes, $i, $astrShowFields[$j]) . "&nbsp;";
                if( $astrShowFields[$j]['type'] == "TEXT" ) {
                        $astrListValues[$i][$j] = mysql_result($intRes, $i, $astrShowFields[$j]['name']);
                }
                elseif( $astrShowFields[$j]['type'] == "INT" ) {
                        $astrListValues[$i][$j] .= mysql_result($intRes, $i, $astrShowFields[$j]['name']);
                }
                elseif( $astrShowFields[$j]['type'] == "INTDATE" ) {
                        $astrListValues[$i][$j] .= dateConvIntDate2Date( mysql_result($intRes, $i, $astrShowFields[$j]['name']) );
                }
                
            }
        }
    }
    $strKeyValues = implode( ";", $astrKeyValues );
}
if( $intTotal == 1 ) {
    $strCounter = "1 / 1";
}
else {
    $strCounter = ($intStart + 1) . " - " . $intEnd . " / " . $intTotal;
}
if( count($astrListValues) > 0 ) {
    //<input type="hidden" name="where" value="?=urlencode($strWhereClause)>">
    //work with this...
?>
<body class="list">
<center><b><?php echo $strTitle?> : </b>
<table>
<form method="post" action="list.php?ses=<?php echo $GLOBALS['sesID']?>" target="f_list" name="form_list">
<input type="hidden" name="searchterms" value="<?php echo $strSearchTerms?>">
<input type="hidden" name="key_values" value="<?php echo $strKeyValues?>">
<input type="hidden" name="page" value="<?php echo $intPage?>">
<input type="hidden" name="selectform" value="<?php echo $strForm?>">

<?php echo $strHiddenTerm?>

    <tr>
        <td align="left">
<?php
if( $intPage > 1 ) {
?>
            <input type="hidden" name="prev" value="0">
            <a class="tinyactionlink" href="#" onclick="self.document.forms[0].prev.value=1; self.document.forms[0].submit(); return false;"> < </a>
            
<?php
}
else {
?>
    &nbsp;
<?php
}
?>
        </td>
        <td align="center">
            <?php echo $strCounter?>
        </td>
        <td align="right">
<?php
if( $intEnd != $intTotal ) {
?>        
            <input type="hidden" name="forw" value="0">
            <a class="tinyactionlink" href="#" onclick="self.document.forms[0].forw.value=1; self.document.forms[0].submit(); return false;"> > </a>
<?php
}
else {
?>
    &nbsp;
<?php
}
?>
        </td>
    </tr>
</form>
</table>
</center>

<table class="list">
    <tr>
<?php
for( $j = 0; $j < count($astrShowFields); $j++ ) {
?>
        <th class="label">
            <?php echo $astrShowFields[$j]['header']?>&nbsp;
        </th>
<?php
}
?>
    </tr>
<?php
    for( $i = 0; $i < count($astrListValues); $i++ ) {
        $strLink = $strMainForm;
        $strLink .= strstr($strMainForm, "?") ? "&" : "?";
        $strLink .= $strPrimaryKey . "=" . $astrPrimaryKeys[$i];
        $strLink .= "&key_name=". $strPrimaryKey;
?>

    <tr class="listrow">
<?php
    for( $j = 0; $j < count($astrListValues[$i]); $j++ ) {
?>
        <td class="label">
            <a class="navilink" href="<?php echo $strLink?>" target="f_main"><?php echo $astrListValues[$i][$j]?>&nbsp;&nbsp;</a> 
        </td>
<?php
    }
?>
    </tr>

<!--    <tr>
        <td>
            <nobr><a class="listlink" href="#" onClick="parent.frset_main.f_main.location.href = '<?php echo $strLink?>'; return false;"><?php echo $astrListValues[$i]?></a></nobr>
        </td>
    </tr>-->
<?php
    }
    $strLink = $strMainForm;
    $strLink .= strstr($strMainForm, "?") ? "&" : "?";
    $strLink .= "new=1";
?>
</table>
<center>
<br>
<a class="actionlink" href="#" onclick="parent.frset_main.f_main.location.href = '<?php echo $strLink?>'; return false;"><?php echo $GLOBALS['locNEW']?></a>
<a class="actionlink" href="#" onclick="window.open('help.php?ses=<?php echo $GLOBALS['sesID']?>&topic=list', '_blank', 'height=400,width=400,menubar=no,scrollbars=yes,status=no,toolbar=no'); return false;"><?php echo $GLOBALS['locHELP']?></a>

</center>
<?php
}
else {
    $strLink = $strMainForm;
    $strLink .= strstr($strMainForm, "?") ? "&" : "?";
    $strLink .= "new=1";
?>
<body class="list">
<center><b><?php echo $strTitle?> :</b></center>
<b><?php echo $GLOBALS['locNOENTRIES']?></b><br><br>
<center>
<a class="actionlink" href="#" onclick="parent.frset_main.f_main.location.href = '<?php echo $strLink?>'; return false;"><?php echo $GLOBALS['locNEW']?></a>
<a class="actionlink" href="#" onclick="window.open('help.php?ses=<?php echo $GLOBALS['sesID']?>&topic=list', '_blank', 'height=400,width=400,menubar=no,scrollbars=yes,status=no,toolbar=no'); return false;"><?php echo $GLOBALS['locHELP']?></a>
</center>
<?php
}
?>
</body>
</html>