<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Georg Ringer <http://www.just2b.com>
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

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'SmoothGallery' for the 'rgsmoothgallery' extension.
 *
 * @author	Georg Ringer <http://www.just2b.com>
 * @package	TYPO3
 * @subpackage	tx_rgsmoothgallery
 */
class tx_rgsmoothgallery_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_rgsmoothgallery_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_rgsmoothgallery_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'rgsmoothgallery';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * Just some intialization, mainly reading the settings in the flexforms
	 *
	 * @param	array		$conf: The PlugIn configuration
	 */	
	function init($conf) {
    $this->conf = $conf; // Storing configuration as a member var
		$this->pi_loadLL(); // Loading language-labels
		$this->pi_setPiVarDefaults(); // Set default piVars from TS
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		
		// Template code
		$this->templateCode = $this->cObj->fileResource($this->conf['templateFile']);
		$this->config['count'] = 0;

		// configuration flexforms
    $this->config['mode'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode', 'sDEF') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'mode', 'sDEF') : $this->conf['mode'];
		$this->config['duration'] =  intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'time', 'sDEF')) ? intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'time', 'sDEF')) : intval($this->conf['duration']);
		$this->config['startingpoint'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpoint', 'sDEF') ? trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpoint', 'sDEF')) : trim($this->conf['startingpoint']);
		$pid = $this->conf['pid'] ? $this->conf['pid'] : $GLOBALS['TSFE']->id;
    $this->conf['startingpointrecords'] = $this->conf['startingpointrecords'] ? $this->conf['startingpointrecords'] : $pid;		
		$this->config['startingpointrecords'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpointrecords', 'sDEF') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpointrecords', 'sDEF') : ($this->conf['startingpointrecords']);
		$this->config['startingpointdam'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpointdam', 'sDEF') ;
		$this->config['startingpointdamcat'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startingpointdamcat', 'sDEF') ;
		$this->config['recursivedamcat'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'recursivedamcat', 'sDEF') ;    
    $this->config['text'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'text', 'sDEF') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'text', 'sDEF') : $this->conf['text'];

		
    // size of images, overwritten by flexforms
    $this->config['width'] = ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'sDEF')) ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'sDEF') : $this->conf['big.']['file.']['maxW'] ;
  #  if ($this->config['width'])  $this->conf['big.']['file.']['maxW'] = $this->config['width'];
    $this->config['height'] = ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'sDEF')) ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'sDEF') : $this->conf['big.']['file.']['maxH'];
  #  if ($this->config['height']) $this->conf['big.']['file.']['maxH'] = $this->config['height'];
   
    $this->config['heightGallery'] = ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'heightgallery', 'sDEF'));
    $this->config['widthGallery'] = ($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'widthgallery', 'sDEF'));
    
    if ( strpos($this->config['width'],'c') ||  strpos($this->config['width'],'m') || strpos($this->config['height'],'c') ||  strpos($this->config['height'],'m') ) {
      $this->conf['big.']['file.']['width']  = $this->config['width'];
      $this->conf['big.']['file.']['height'] = $this->config['height'];
      $this->conf['big2.']['file.']['10.']['file.']['width']  = $this->config['width'];
      $this->conf['big2.']['file.']['10.']['file.']['height'] = $this->config['height'];
     
      unset($this->conf['big.']['file.']['maxW']);
      unset($this->conf['big.']['file.']['maxH']);
      unset($this->conf['big2.']['file.']['10.']['file.']['maxW']);
      unset($this->conf['big2.']['file.']['10.']['file.']['maxH']);
    } else {
      if ($this->config['width'])  {
        $this->conf['big.']['file.']['maxW'] = $this->config['width'];
        $this->conf['big2.']['file.']['10.']['file.']['maxW']= $this->config['width'];
      }
      if ($this->config['height']) {
        $this->conf['big.']['file.']['maxH'] = $this->config['height'];
        $this->conf['big2.']['file.']['10.']['file.']['maxH']= $this->config['height'];
      }
      if (!$this->config['heightGallery']) $this->config['heightGallery'] = $this->config['height'];
      if (!$this->config['widthGallery']) $this->config['widthGallery'] = $this->config['width'];      
    }
    
    /*
     * Advanced settings
     */      
    $this->config['infopane'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'infopane', 'advanced');
    $this->config['thumbopacity'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbopacity', 'advanced');  
    $this->config['slideinfozoneopacity'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'slideinfozoneopacity', 'advanced');  
    $this->config['thumbspacing'] =  intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'thumbspacing', 'advanced'));    

		$this->config['watermark'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'watermark', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'watermark', 'advanced') : $this->conf['watermarks'];
		$this->config['limitImagesDisplayed'] =  	intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'limitImagesDisplayed', 'advanced')) 	? intval($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'limitImagesDisplayed', 'advanced')) 	: intval($this->conf['limitImagesDisplayed']);
    
		$this->config['showLightbox'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'lightbox', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'lightbox', 'advanced') : $this->conf['lightbox'];
		$this->config['showThumbs'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showThumbs', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showThumbs', 'advanced') : $this->conf['showThumbs'];		
		$this->config['showArrows'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'arrows', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'arrows', 'advanced') : $this->conf['arrows'];
		$this->config['advancedSettings'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'advancedsettings', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'advancedsettings', 'advanced') : $this->conf['advancedSettings'];    
		$this->config['externalthumbs'] =  $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'externalthumbs', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'externalthumbs', 'advanced') : $this->conf['externalThumbs'];
    $this->config['externalControl'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'externalcontrol', 'advanced') ? $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'externalcontrol', 'advanced') : $this->conf['externalControl'];
    
  }


	/**
	 * The main method of the PlugIn
	 * for showing the SmoothGallery	 
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The gallery
	 */
	function main($content,$conf)	{
		global $TYPO3_DB;
    $this->init($conf);	
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj=0;	// Configuring so caching is expected. 
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin
		
		if ($this->conf['pathToJdgalleryJS']=='') {
      return $this->pi_getLL('errorIncludeStatic');
    } else {
      // get the needed js to load the gallery and to start it
      $content .= $this->getJs(
        $this->config['showLightbox'],
        $this->config['showThumbs'],
        $this->config['showArrows'],
        $this->config['duration'],
        $this->config['width'],
        $this->config['height'],
        $this->config['widthGallery'],
        $this->config['heightGallery'],        
        $this->config['advancedSettings'],
        $this->cObj->data['uid'],
        $this->conf
      );

    	// depending on the chosen settings the images come from different places
  	 $content.=$this->getImageDifferentPlaces($this->config['limitImagesDisplayed']);
  	
  	 #return $this->pi_wrapInBaseClass($content);
  	 return '<div class="tx-rgsmoothgallery-pi1 rgsgnest'.$this->cObj->data['uid'].'">'.$content.'</div>';
    }

	} # end main


	/**
	 * Just some divs needed for the gallery
	 *
	 * @param	string/int   $uniqueId: A unique ID to have more than 1 galleries on 1 page
	 * @return	The opened divs
	 */	
	function beginGallery($uniqueId,$limitImages=0) {
		if ($limitImages==1) {
			$content = '<div class="content"><div class="myGallery-NoScript" id="myGallery-NoScript'.$uniqueId.'">';
		}
		else {
			$content =  '<div class="content"><div class="myGallery" id="myGallery'.$uniqueId.'">';
		}
		
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraBeginGalleryHook'])) {
      foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraBeginGalleryHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$content = $_procObj->extraBeginGalleryProcessor($content, $limitImages,$this);
      }
    } 
    return $content; 
  }

	/**
	 * Just some divs needed for the gallery
	 *
	 * @return	The closed divs
	 */	  
  function endGallery() {
    $content = '</div></div>';
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraEndGalleryHook'])) {
      foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraEndGalleryHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$content = $_procObj->extraEndGalleryProcessor($content, $this);
      }
    }    
    return $content;
  }


	/**
	 * get the images out of a directory
	 *
	 * @param	int		$limitImages: How many images to return; default=0 list all
	 * @return	image(s)
	 */	
  function getImagesDirectory($limitImages=0) {  		 
        if (is_dir($this->config['startingpoint'])) {
  		  $images = array(); 
  		  $images = $this->getFiles($this->config['startingpoint']);      	
        // randomise and limit image items returned from images array
        // also useful to limit items in array to 1 item for use when no javascript in browser
        // if $limitImages=0 then this if statement is bypassed and all images in images array returned for processing
        if ($limitImages>0) {
        	$images=$this->getSlicedRandomArray($images,0,$limitImages);
        }

        $content.= $this->beginGallery($this->cObj->data['uid'],$limitImages);

        // read the description from field in flexforms
      	$caption = explode("\n",$this->config['text']);
        
        // add the images
        foreach ($images as $key=>$value) {
          $path = $this->config['startingpoint'].$value;
         
          // caption text
          $text =explode('|',$caption[$key]);
          
          // add element to slideshow
          $content.=$this->addImage(
  			    $path,
            $text[0], 
            $text[1],
            $this->config['showThumbs'],
            $this->config['showLightbox'],
            $path,
            $limitImages
          );
        
        } # end foreach file
        
  		  $content.=$this->endGallery();
  		} # end is_dir 
  		return $content;
  }


	/**
	 * get the images out of records a user created in the backend before
	 *
	 * @param	int		$limitImages: How many images to return; default=0 list all
	 * @return	image(s)
	 */	
  function getImagesRecords($limitImages=0) {
  		//prepare query
  		$sort = 'sorting';
  		$fields = 'title,image,description,l18n_parent';
  		$tables = 'tx_rgsmoothgallery_image';
  		$temp_where='pid IN ('. $this->config['startingpointrecords'] . ') AND hidden=0 AND deleted=0 AND sys_language_uid = '.$GLOBALS['TSFE']->sys_language_content;
  	
      $content.=$this->beginGallery($this->cObj->data['uid'],$limitImages);
      
      // add the images
      // randomise and limit image items returned from images array
      // also useful to limit items in array to 1 item for use when no javascript in browser
      // if $limitImages=0 then this if statement is bypassed and all images in images array returned for processing
      if ($limitImages>0) {
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $temp_where,'', 'rand()',$limitImages);
      } else {
  		  $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $temp_where,'', $sort,$limit);
  		}
  		$this->sys_language_mode = $this->conf['sys_language_mode']?$this->conf['sys_language_mode'] : $GLOBALS['TSFE']->sys_language_mode;
  		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    	  
      	if ($GLOBALS['TSFE']->sys_language_content) {
    			$OLmode = ($this->sys_language_mode == 'strict' ? 'hideNonTranslated' : '');
    			$row = $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_rgsmoothgallery_image',$row, $GLOBALS['TSFE']->sys_language_content, $OLmode);
    		}  	

        if ($row['image']=='') {
          $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('image', $tables, 'uid='.$row['l18n_parent']);
          $row2 = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2);  
          $row['image'] = $row2['image'];   
        }

        $path = 'uploads/tx_rgsmoothgallery/'.$row['image'];
       
        // add element to slideshow
        $content.=$this->addImage(
			    $path,
          $row['title'], 
          $row['description'], 
          $this->config['showThumbs'],
          $this->config['showLightbox'],
          $path,
          $limitImages
        );
      
      	$i++;
      } # end foreach file
      
		$content.=$this->endGallery();
  		return $content;
  }


	/**
	 * get the images out of DAM
	 *
	 * @param	int		$limitImages: How many images to return; default=0 list all
	 * @return	image(s)
	 */	
  function getImagesDam($limitImages=0) {
      #  echo t3lib_div::view_array($images);
      
      // get all the files
      $images = tx_dam_db::getReferencedFiles('tt_content',$this->cObj->data['uid'],'rgsmoothgallery','tx_dam_mm_ref');

        // randomise and limit image items returned from images array
        // also useful to limit items in array to 1 item for use when no javascript in browser
        // if $limitImages=0 then this if statement is bypassed and all images in images array returned for processing
        if ($limitImages>0) {
          $test = ($images['files']);
          $test = $this->getSlicedRandomArray($test,0,$limitImages);
          $images['files'] = $test;
        }
    # echo t3lib_div::view_array($images['files']); 
  	  $content.=$this->beginGallery($this->cObj->data['uid'],$limitImages);
  	  
  	  // add image
      foreach ($images['files'] as $key=>$path) {    
        // get data from the single image
        $fields = 'title,description';
        $tables = 'tx_dam';
        $temp_where='uid = '.$key;
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $temp_where);
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

        // add element to slideshow
        $content.=$this->addImage(
			    $path,
          $row['title'], 
          $row['description'], 
          $this->config['showThumbs'],
          $this->config['showLightbox'],
          $path,
          $limitImages
        );
      }
      
      $content.=$this->endGallery();
      return $content;
    }

	/**
	 * get the images out of DAM
	 *
	 * @param	int		$limitImages: How many images to return; default=0 list all
	 * @return	image(s)
	 */	
  function getImagesDamCat($limitImages=0) {
	  $content.=$this->beginGallery($this->cObj->data['uid'],$limitImages);
	  
	  // add image
    $list= str_replace('tx_dam_cat_', '',$this->config['startingpointdamcat']);

    $listRecursive = $this->getRecursiveDamCat($list,$this->config['recursivedamcat']);
    $listArray = explode(',',$listRecursive);
    $files = Array();
		foreach($listArray as $cat) {
						
			// add images from categories
			$fields = 'tx_dam.uid,tx_dam.title,tx_dam.description,tx_dam.file_name,tx_dam.file_path';
			$tables = 'tx_dam,tx_dam_mm_cat';
			$temp_where = 'tx_dam.deleted = 0 AND tx_dam.hidden=0 AND tx_dam_mm_cat.uid_foreign='.$cat.' AND tx_dam_mm_cat.uid_local=tx_dam.uid';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $tables, $temp_where);
			
      while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
        $files[$row['uid']] = $row; # just add the image to an array
			}
		}
		
    if ($limitImages>0) {
      $files = $this->getSlicedRandomArray($files,0,$limitImages);
    }	
    
		// add the image for real
		foreach ($files as $key=>$row) {
      $path =  $row['file_path'].$row['file_name'];

      // add element to slideshow
      $content.=$this->addImage(
		    $path,
        $row['title'], 
        $row['description'], 
        $this->config['showThumbs'],
        $this->config['showLightbox'],
        $path,
        $limitImages
      );	
    }			
        
      
    $content.=$this->endGallery();
    return $content;
  }

	/**
	 * Loads all the needed javascript stuff and 
	 * does the configuration of the gallery	 	 
	 *
	 * @param	boolean  $lightboxVal: Lightbox activated=
	 * @param	boolean  $thumbsVal: Thumbnail preview activated?
	 * @param	boolean  $arrowsVal: Arrows to neighbour images activated?
	 * @param	string   $durationVal: If automatic slideshow the value of the delay
	 * @param	int      $width: Width of gallery	 
   * @param	int      $height: Height of gallery
	 * @param	string   $advancedSettings: Advanced configuration
   * @param	string/int   $uniqueId: A unique ID to have more than 1 galleries on 1 page
	 * $param array    $conf: $configuration-array	 
	 * @return	The gallery
	 */  
  function getJs($lightboxVal,$thumbsVal,$arrowsVal,$durationVal,$width,$height,$widthGallery,$heightGallery,$advancedSettings,$uniqueId,$conf,$overrideJS='') {
    $this->conf =$conf;

    // path to js + css
		$GLOBALS['TSFE']->additionalHeaderData['rgsmoothgallery'] = '
        <script src="'.$this->getPath($this->conf['pathToMootools']).'" type="text/javascript"></script>
        <script src="'.$this->getPath($this->conf['pathToJdgalleryJS']).'" type="text/javascript"></script>
        <script src="'.$this->getPath($this->conf['pathToSlightboxJS']).'" type="text/javascript"></script>
        <link rel="stylesheet" href="'.$this->getPath($this->conf['pathToJdgalleryCSS']).'" type="text/css" media="screen" />
        <link rel="stylesheet" href="'.$this->getPath($this->conf['pathToSlightboxCSS']).'" type="text/css" media="screen" />
      ';
    
    if ($this->config['externalControl']==1) {
      $externalControl1 = 'var myGallery'.$uniqueId.';';
    } else {
      $externalControl2 = 'var';  
    }
      

    // inline CSS for different size of gallery 
    $widthGallery = $widthGallery ? 'width:'.$widthGallery.'px;' : '';
    $heightGallery = $heightGallery ? 'height:'.$heightGallery.'px;' :'';
    if ($heightGallery!='' || $widthGallery!='') {
      $GLOBALS['TSFE']->additionalCSS['rgsmoothgallery'.$uniqueId] = '#myGallery'.$uniqueId.' {'.$widthGallery.$heightGallery.'}';
    }
    
    // inline CSS for the loading bar if plugin not loaded and for the given height of the gallery
    $GLOBALS['TSFE']->additionalCSS['rgsmoothgallery'.$uniqueId] .= ' .rgsgnest'.$uniqueId.' { '.$widthGallery.$heightGallery.' }';
    
    if ($this->conf['rgsmoothgallerylinks']==1) {
      $GLOBALS['TSFE']->additionalCSS['rgsmoothgallery'.$uniqueId] .= ' .rgsglinks'.$uniqueId.' { '.$widthGallery.' }';
    }

    // configuration of gallery
    $lightbox = ($lightboxVal==1) ? 'true' : 'false';
    $lightbox2= ($lightboxVal==1) ? 'var mylightbox = new Lightbox();' : '';
    $duration = ($durationVal) ? 'timed:true,delay: '.$durationVal : 'timed:false';
    $thumbs   = ($thumbsVal==1) ? 'true' : 'false';
    $arrows   = ($arrowsVal==1) ? 'true' : 'false';
    
    // advanced settings (from TS + tab flexform configuration)
    $advancedSettings.=  ($this->config['infopane']) ? 'showInfopane: false,' : ''; 
    if ($this->config['thumbopacity'] && $this->config['thumbopacity'] > 0 && $this->config['thumbopacity']<=1) $advancedSettings.= 'thumbOpacity: '.$this->config['thumbopacity'].',';
    if ($this->config['infopane'] && $this->config['slideinfozoneopacity'] && $this->config['slideinfozoneopacity'] > 0 && $this->config['slideinfozoneopacity']<=1) $advancedSettings.= 'slideInfoZoneOpacity: '.$this->config['slideinfozoneopacity'].',';   
    $advancedSettings.=  ($this->config['thumbspacing']) ? 'thumbSpacing: '.$this->config['thumbspacing'].',' : ''; 

    // external thumbs
    $advancedSettings.= ($this->config['externalthumbs']) ? 'useExternalCarousel:true,carouselElement:$("'.$this->config['externalthumbs'].'"),' : '';
      # 
    
    // js needed to load the gallery and to get it started  
    if ($overrideJS!='') {
      $js = $overrideJS;
    } else {
      $js.= '
    		<script type="text/javascript">'.$externalControl1.'
    			function startGallery'.$uniqueId.'() {
    			  if(window.gallery'.$uniqueId.')
    			    {
    			    try
    			      {
    				    '.$externalControl2.' myGallery'.$uniqueId.' = new gallery($(\'myGallery'.$uniqueId.'\'), {
    					    '.$duration.',
    					      showArrows: '.$arrows.',
                  showCarousel: '.$thumbs.',
                  textShowCarousel: \''.$this->pi_getLL('textShowCarousel').'\',
                  embedLinks:'.$lightbox.',
                  '.$advancedSettings.'
    					    lightbox:true
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
    		</script>';
    	if ($this->conf['noscript']==1) {
    	 $js.= '<noscript>
    		'.$this->getImageDifferentPlaces(1).'
    		</noscript>';
  		}
    }
    return $js;
  }


	/**
	 * depending on the chosen settings the images come from different places 
	 *
	 * @param	string  $limitImages: How many images to return; default=0 list all
	 * @return	The image(s)
	 */ 
  function getImageDifferentPlaces($limitImages=0) {
  	if ($this->config['mode']=='DIRECTORY') {
  		$content.=$this->getImagesDirectory($limitImages);
  	} elseif ($this->config['mode']=='RECORDS') {
  		$content.=$this->getImagesRecords($limitImages);
    } elseif ($this->config['mode']=='DAM') {
  		$content.=$this->getImagesDam($limitImages);
    } elseif ($this->config['mode']=='DAMCAT') {
  		$content.=$this->getImagesDamCat($limitImages);
    } 
    
    // hook
    if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraDifferentPlaces'])) {
      foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraDifferentPlaces'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$content = $_procObj->extraBeginGalleryProcessor($content, $limitImages,$this);
      }
    }     
  	return $content;
  }

	/**
	 * Adds a single image to the gallery 
	 *
	 * @param	string  $path: Path to the image
	 * @param	string  $title: Title for the image
	 * @param	string  $description: Description for the image
	 * @param	string  $thumb: Url to the thumbnail image
	 * @param	string  $lightbox: Url to the lightbox image
	 * @param	string  $uniqueID: Unique-ID to identify an image (uid or path)
	 * @param	string  $limitImages: How many images to return; default=0 list all
	 * @return	The single image
	 */ 	
	function addImage($path,$title,$description,$thumb,$lightbox,$uniqueID,$limitImages=0) {
	  // count of images
	  if ($limitImages > 1 || $limitImages==0) {
      $this->config['count']++;
	  }
    // just add the wraps if there is a text for it
    $title = (!$title) ? '' : "<h3>$title</h3>";
    $description = (!$description) ? '' : "<p>$description</p>";
    
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
  	$lightBoxImage='<a href="'.$lightbox.'" title="'.$this->pi_getLL('textOpenImage').'" class="open"></a>';

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
      <div class="imageElement">'.$title.$description.
        $lightBoxImage.'
        <img src="'.$bigImage.'" class="full" />
        '.$thumbImage.'
      </div>';

		// Adds hook for processing the image
		$config['path'] = $path;
    $config['title'] = $title;
		$config['description'] = $description;
		$config['uniqueID'] = $uniqueID;
		$config['thumb'] = $thumb;
		$config['large'] = $large;
		$config['lightbox'] = $lightbox;
		$config['limitImages'] = $limitImages;
		$config['lightBoxCode'] = $lightBoxImage;
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraImageHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgsmoothgallery']['extraImageHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$singleImage = $_procObj->extraImageProcessor($singleImage,$config, $this);
			}
		}      
      
    return $singleImage;
  }

	/**
	 * Gets all image files out of a directory 
	 *
	 * @param	string  $path: Path to the directory
	 * @return Array with the images
	 */ 
  function getFiles($path, $extra = "") {
    // check for needed slash at the end
		$length = strlen($path);
		if ($path{$length-1}!='/') { 
      $path.='/';
    }
    
    $imagetypes = $this->conf["filetypes"] ? explode(',', $this->conf["filetypes"]) : array(
        'jpg',
        'jpeg',
        'gif',
        'png'
    );

    if($dir = dir($path)) {
        $files = Array();

        while(false !== ($file = $dir->read())) {
            if ($file != '.' && $file != '..') {
                $ext = strtolower(substr($file, strrpos($file, '.')+1));
                if (in_array($ext, $imagetypes)) {
                    array_push($files, $extra . $file);
                }
                else if ($this->conf["recursive"] == '1' && is_dir($path . "/" . $file)) {
                    $dirfiles = $this->getFiles($path . "/" . $file, $extra . $file . "/");
                    if (is_array($dirfiles)) {
                        $files = array_merge($files, $dirfiles);
                    }
                }
            }
        }

        $dir->close();
        #$files = shuffle($files);
        #echo t3lib_div::view_array($files);
        return $files;
    }
  } # end getFiles
  
	/**
	 * Gets the path to a file, needed to translate the 'EXT:extkey' into the real path
	 *
	 * @param	string  $path: Path to the file
	 * @return the real path
	 */
  function getPath($path) {
    if (substr($path,0,4)=='EXT:') {
      $keyEndPos = strpos($path, '/', 6);
      $key = substr($path,4,$keyEndPos-4);
      $keyPath = t3lib_extMgm::siteRelpath($key);
      $newPath = $keyPath.substr($path,$keyEndPos+1);
      return $newPath;
    }	else {
      return $path;
    }
  } # end getPath
    

	/**
	 * Random view of an array and slice it afterwards, preserving the keys
	 *
	 * @param	array  $array: Array to modify
	 * @param	array  $offset: Where to start the slicing
	 * @param	array  $length: Length of the sliced array
	 * @return the randomized and sliced array
	 */
  function getSlicedRandomArray ($array,$offset,$length) {
    // shuffle
    while (count($array) > 0) {
      $val = array_rand($array);
      $new_arr[$val] = $array[$val];
      unset($array[$val]);
    }
    $result=$new_arr;
    
    // slice
    $result2 = array();
    $i = 0;
    if($offset < 0)
        $offset = count($result) + $offset;
    if($length > 0)
        $endOffset = $offset + $length;
    else if($length < 0)
        $endOffset = count($result) + $length;
    else
        $endOffset = count($result);
   
    // collect elements
    foreach($result as $key=>$value)
    {
        if($i >= $offset && $i < $endOffset)
            $result2[$key] = $value;
        $i++;
    }
    return $result2;
  }       	

	/**
	 * get a list of recursive categories
	 *
	 * @param	string		$id: comma seperated list of ids
	 * @param	int		$level: the level for recursion 	 
	 * @return	image(s)
	 */	
  function getRecursiveDamCat($id,$level=0) {
    $result = $id.','; # add id of 1st level 
    $idList = explode(',',$id);
    
    if ($level > 0) {
      $level--;
      
      foreach ($idList as $key=>$value) {
        $where = 'hidden=0 AND deleted=0 AND parent_id='.$id;
        $res= $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'tx_dam_cat', $where);
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){	
          $all[$row['uid']]=$row['uid'];
          $rec = $this->getRecursiveDamCat($row['uid'],$level);
          if ($rec!='')  {
            $result.=$rec.',';
          }
        }
      } # end for each
    } # end if level
     	
    $result = str_replace(',,',',',$result);
    $result = substr($result,0,-1);
    return $result;
  }

}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsmoothgallery/pi1/class.tx_rgsmoothgallery_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgsmoothgallery/pi1/class.tx_rgsmoothgallery_pi1.php']);
}

?>
