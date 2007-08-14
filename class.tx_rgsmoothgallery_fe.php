<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <typo3@ringerge.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook for the 'rgsmoothgallery' extension.
 *
 * @author	Georg Ringer <http://www.rggooglemap.com/>
 */

class tx_rgsmoothgallery_fe{

  // hook for tt_news
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.	
		$this->pObj = &$pObj;
		$this->realConf = $pObj;
		
		// configuration array of rgSmoothGallery
    $rgsgConf = $this->realConf->conf['rgsmoothgallery.'];
		
		// if the configuration is available, otherwise just do nothing
    if ($rgsgConf) {

      // unique ID > uid of the record
      $uniqueId = $markerArray['###NEWS_UID###'];
      
      // query for the images & caption
      $field = 'imagecaption,image';
      $table = 'tt_news';
      $where = 'uid = '.$uniqueId;   
      $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($field,$table,$where);
      $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
      if ($GLOBALS['TSFE']->sys_language_content) {
  			$OLmode = ($this->sys_language_mode == 'strict'?'hideNonTranslated':'');
  			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tt_news', $row, $GLOBALS['TSFE']->sys_language_content, '');
  		}

      // needed fields: image & imagecaption
      $images = explode(',',$row['image']);
    	$caption = explode("\n",$row['imagecaption']);
    		
      // If there are any images and minimum count of images is reached
      if ($row['image'] && count($images) >= $rgsgConf['minimumImages']) {
    	  // call rgsmoothgallery
        require_once( t3lib_extMgm::siteRelpath('rgsmoothgallery').'pi1/class.tx_rgsmoothgallery_pi1.php');
    	  $this->gallery = t3lib_div::makeInstance('tx_rgsmoothgallery_pi1');
    	  
    	  // if no js is available
    	  $noJsImg =   $rgsgConf['big.'];
        $noJsImg['file'] = 'uploads/pics/'.$images[0];   

        if ($rgsgConf['externalControl']==1) {
          $externalControl1 = 'var myGallery'.$uniqueId.';';
        } else {
          $externalControl2 = 'var';  
        }
              
    	  // configuration
    	  $configuration = '		
  
    		<script type="text/javascript">'.$externalControl1.'
    			function startGallery'.$uniqueId.'() {
    			  if(window.gallery'.$uniqueId.')
    			    {
    			    try
    			      {
    				    '.$externalControl2.' myGallery'.$uniqueId.' = new gallery($(\'myGallery'.$uniqueId.'\'), {
    					    '.$rgsgConf['settings'].'
    				    });
    				    var mylightbox = new Lightbox();
    				    }catch(error){
    				    window.setTimeout("startGallery'.$uniqueId.'();",2500);
    				    }
    				  }else{
    				  window.gallery'.$uniqueId.'=true;
    				  if(this.ie)
    				    {
    				    window.setTimeout("startGallery'.$uniqueId.'();",3000);
    				    }else{
    				    window.setTimeout("startGallery'.$uniqueId.'();",100);
    				    }
    				  }
    			}
    			window.onDomReady(startGallery'.$uniqueId.');
    		</script>
      <noscript>
  		  <div><img src="'.$this->cObj->IMG_RESOURCE($noJsImg).'"  /></div>
  		</noscript>
        ';       
         
        // get the JS

      
        $content =$this->gallery->getJs(1,1,1,0,$rgsgConf['width'],$rgsgConf['height'],$rgsgConf['width'],$rgsgConf['height'],'',$uniqueId,$rgsgConf,$configuration);
        // Begin the gallery
        $content.=  $this->gallery->beginGallery($uniqueId);
        // add the images
        $i=0;
        foreach ($images as $key=>$value) {
          $path = 'uploads/pics/'.$value;
          // single Image
          $imgTSConfigThumb = $rgsgConf['thumb.'];
          $imgTSConfigThumb['file'] = $path;
          $imgTSConfigBig =   $rgsgConf['big.'];
          $imgTSConfigBig['file'] = $path;        
          $imgTSConfigLightbox = $rgsgConf['lightbox.'];
          $imgTSConfigLightbox['file'] = $path;        
          $lightbox = ($this->config['showLightbox']==1) ? $this->cObj->IMG_RESOURCE($imgTSConfigLightbox) : $this->cObj->IMG_RESOURCE($imgTSConfigLightbox);
         
          // caption text
          $text =explode('|',$caption[$i]);
          
          // add image

                  $content.=$this->addImage(
			    $path,
          $text[0], 
            $text[1],
          true,
          true,
          $path,
          $limitImages
        );
    			$i++;
        } # end foreach file
        
        // end of image    	 
        $content.=$this->gallery->endGallery(); 
     
        // write new gallery into the marker    
        $markerArray['###NEWS_IMAGE###'] ='<div class="news-single-img">'.$content.'</div>';
      } # end if ($row['image']..
    } # end if ($rgsgConf) {

		return $markerArray;
	} #end extraItemMarkerProcessor
	
	function addImage($path,$title,$description,$thumb,$lightbox,$uniqueID,$limitImages=0) {
    // there is a value needed because of tidy
    $title = (!$title) ? '&nbsp;' : $title;
    $description = (!$description) ? '&nbsp;' : $description;

    //  generate images
    if ($this->config['watermark']) {
      $imgTSConfigBig = $this->conf['big2.'];
      $imgTSConfigBig['file.']['10.']['file'] = $path;
      $imgTSConfigLightbox = $this->conf['lightbox2.'];
      $imgTSConfigLightbox['file.']['10.']['file'] = $path; 
    } else {
      $imgTSConfigBig = $this->conf['big.'];
      $imgTSConfigBig['file'] = $path;
      $imgTSConfigLightbox = $this->conf['lightbox.'];
      $imgTSConfigLightbox['file'] = $path;               
    }  
    $bigImage = $this->cObj->IMG_RESOURCE($imgTSConfigBig);

    $lightbox =  ($lightbox=='#' || $lightbox=='' || $this->config['showLightbox']!=1) ? 'javascript:void(0)' :  $this->cObj->IMG_RESOURCE($imgTSConfigLightbox);
  	$lightBoxImage='<a href="'.$lightbox.'" title="Open Image" class="open"></a>';

    if ($thumb) {
      $imgTSConfigThumb = $this->conf['thumb.'];
      $imgTSConfigThumb['file'] = $path;     
      $thumbImage = '<img src="'.$this->cObj->IMG_RESOURCE($imgTSConfigThumb).'" class="thumbnail" />';
    }

	  // if just 1 image should be returned
    if ($limitImages==1) {
    	return '<img src="'.$bigImage.'" class="full" />';
    }

    // build the image element    
    $singleImage .= '
      <div class="imageElement">
        <h3>'.$title.'</h3>
        <p>'.$description.'</p>
        '.$lightBoxImage.'
        <img src="'.$bigImage.'" class="full" />
        '.$thumbImage.'
      </div>';      
    return $singleImage;
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsmoothgallery/class.tx_rgsmoothgallery_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsmoothgallery/class.tx_rgsmoothgallery_fe.php']);
}

?>
