<?php

function user_rgsg($content,$conf) {
 $split=strpos($GLOBALS['TSFE']->currentRecord,':');
 $id = substr($GLOBALS['TSFE']->currentRecord,$split+1);
    $where = 'uid ='.$id;
    $table = 'tt_content';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('imagewidth,imageheight',$table,$where,$groupBy='',$orderBy,$limit='');
$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

$css .= ($row['imagewidth']) ?  'width:'.$row['imagewidth'].'px;' : '';
$css .= ($row['imageheight']) ?  'height:'.$row['imageheight'].'px;' : '';
 $GLOBALS['TSFE']->additionalCSS['rgsmoothgallery'.$id] = '#myGallery'.$id.' {'.$css.'}';

    $path = t3lib_extMgm::siteRelpath('rgsmoothgallery').'res/';
		$GLOBALS['TSFE']->additionalHeaderData['rgsmoothgallery'] = '
        <script src="'.$path.'scripts/mootools.js" type="text/javascript"></script>
        <script src="'.$path.'scripts/jd.gallery107.js" type="text/javascript"></script>
        <link rel="stylesheet" href="'.$path.'css/jd.gallery.css" type="text/css" media="screen" charset="utf-8" />
        <script src="'.$path.'scripts/slightbox107.js" type="text/javascript"></script>
        <link rel="stylesheet" href="'.$path.'css/slightbox.css" type="text/css" media="screen" charset="utf-8" />
      ';



return  $content;
}

function user_test2($content,$conf) {

#echo t3lib_div::view_array($conf);
echo '<pre>';
$GLOBALS['TSFE']->currentRecord;
echo '</pre>';
return $GLOBALS['TSFE']->currentRecord;
}


?>
