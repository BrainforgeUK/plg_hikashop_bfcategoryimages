<?php
/**
 * @copyright	Copyright (C) 2016 brainforge (www.brainforge.co.uk). All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Joomla User plugin
 *
 * @package		Joomla.Plugin
 * @subpackage	Hikashop.joomla
 */
class plgHikashopBFcategoryImages extends JPlugin {

  static $_params = null;
  static $_views = array();
  static $_js1;
  static $_js2;
  static $_js3;
  static $_fadeintime = 400;
  static $_fadeouttime = 400;
  static $_displaytime = 2000;
  static $_categorymode = 'E';
  static $_categories = array();
  static $_productmode = 'E';
  static $_products = array();

  public function __construct($subject, $config) {
    parent::__construct($subject, $config);
    self::loadParams();
  }

  private function loadParams() {
    if (!empty(self::$_params)) return true;
    $plugin = JPluginHelper::getPlugin('hikashop', 'bfcategoryimages');
    if (empty($plugin)) return false;
    self::$_params = new JRegistry($plugin->params);
    return !empty(self::$_params);
  }

  private function getParam($name, $default=null) {
    if (!self::loadParams()) return $default;
    return self::$_params->get($name, $default);
  }

  public function onHikashopBeforeDisplayView(&$view) {
    if (JFactory::getApplication()->isSite()) {
      switch(@$view->ctrl) {
        case null:
        case '':
          break;
        case 'category':
          ob_start();
        default:
          self::$_views[] = $view->ctrl;
          break;
      }
    }

    if (JFactory::getApplication()->isAdmin()) {
    }
  }
  
  public function onHikashopAfterDisplayView(&$view) {
    if (JFactory::getApplication()->isSite()) {
      if (!empty(self::$_views)) {
        switch(array_pop(self::$_views)) {
          case 'category':
            $html = ob_get_contents();
            ob_end_clean();

            plgHikashopBFcategoryImages::$_js1 = '';
            plgHikashopBFcategoryImages::$_js2 = '';
            plgHikashopBFcategoryImages::$_js3 = '';
            plgHikashopBFcategoryImages::$_fadeintime   = plgHikashopBFcategoryImages::getParam('fadeintime', 400);
            plgHikashopBFcategoryImages::$_fadeouttime  = plgHikashopBFcategoryImages::getParam('fadeouttime', 400);
            plgHikashopBFcategoryImages::$_displaytime  = plgHikashopBFcategoryImages::getParam('displaytime', 2000);
            plgHikashopBFcategoryImages::$_categorymode = plgHikashopBFcategoryImages::getParam('categorymode', 'E');
            plgHikashopBFcategoryImages::$_categories   = plgHikashopBFcategoryImages::getParam('categories');
            plgHikashopBFcategoryImages::$_productmode  = plgHikashopBFcategoryImages::getParam('productmode', 'E');
            plgHikashopBFcategoryImages::$_products     = plgHikashopBFcategoryImages::getParam('products');
            
            $html = preg_replace_callback(
                            '/(<div [^>]+hikashop_category_image[^>]+>[^<]*<a[^>]*href=")([^"]*)("[^>]*>[^<]*)(<img)([^>]*src=")([^"]*)("[^>]*>)([^<]*<\\/a>[^<]*<\\/div>)/sm',
                            function($matches) {
                              switch (count($matches)) {
                                case 0:
                                  return '';
                                case 9:
                                  $href = explode('/', $matches[2]);
                                  $catid = plgHikashopBFcategoryImages::getCatId(array_pop($href));
                                  $images = plgHikashopBFcategoryImages::getImagesForProductsInCategory($catid);
                                  if (count($images) < 2) {
                                    return $matches[0];
                                  }
                                  
                                  $helperImage = hikashop_get('helper.image');
                                  $dirname = dirname($matches[6]) . '/';
                                  $thumbparams = sscanf(basename($dirname), '%dx%d%s');

                                  $html = $matches[1] . $matches[2] . $matches[3];
                                  $html .= '<div id="hikashop_category_product_images_for_' . $catid . '" class="hikashop_category_product_images_for" onmouseenter="BFCIhover=1;" onmouseleave="BFCIhover=0;">';
                                  foreach($images as $id=>$image) {
                                    $helperImage->getThumbnail($image,
                                                         array('width'=>$thumbparams[0],
                                                               'height'=>$thumbparams[1]),
                                                         array('default' => 1,
                                                               'scale' => 'inside',
                                                               'forcesize' => (@$thumbparams[2] == 'f'))
                                                 );
                                    $html .= '<div class="hikashop_category_product_images">';
                                    $html .= $matches[4];
                                    $html .= ' id="hikashop_category_image_for_' . $catid . '_' . $id . '"';
                                    $html .= $matches[5];
                                    $html .= $dirname . $image;
                                    $html .= $matches[7];
                                    $html .= '</div>';
                                  }
                                  $html .= '</div>';
                                  $html .= $matches[8];

                                  plgHikashopBFcategoryImages::$_js1 .= 'var BFCIindex' . $catid . ' = 0;
';
                                  plgHikashopBFcategoryImages::$_js2 .= 'BFCIfadeoutAll(' . count($images) . ',' . $catid . ');
';
                                  plgHikashopBFcategoryImages::$_js3 .= 'BFCIindex' . $catid . ' = BFCIswapImage(BFCIindex' . $catid . ',' . count($images) . ',' . $catid . ',' . plgHikashopBFcategoryImages::$_fadeouttime . ',' . plgHikashopBFcategoryImages::$_fadeintime . ');
';                                  
                                  return $html;
                                 default:
                                  return $matches[0];
                              }
                                               }, $html);
            echo $html;
            if (!empty(plgHikashopBFcategoryImages::$_js1)) {
              JFactory::getDocument()->addScriptDeclaration(plgHikashopBFcategoryImages::$_js1 . '
var BFCIhover = 0;              
jQuery(document).ready(function() {
' . plgHikashopBFcategoryImages::$_js2 . '
  setInterval(function() {
    if (BFCIhover == 0) {
' . plgHikashopBFcategoryImages::$_js3 . '
    }
  }, ' .  plgHikashopBFcategoryImages::$_displaytime . ');
});
');
              JHtml::script(JUri::base() . 'plugins/hikashop/bfcategoryimages/bfcategoryimages.js');
              JHtml::stylesheet(JUri::base() . 'plugins/hikashop/bfcategoryimages/bfcategoryimages.css');
            }
            break;
          default:
            break;
        }
      }
    }
  }
  
  function getCatId($catLink) {
    $catid = intval($catLink);
    if (empty($catid)) {
  		$db = JFactory::getDBO();
  		$query = 'SELECT category.category_id '.
          			' FROM '.hikashop_table('category').' AS category '.
                "WHERE category.category_alias =  '" . $catLink ."'";
  		$db->setQuery($query);
			$catid = $db->loadResult();
    }
    return $catid;
  }
  
	function &getImagesForProductsInCategory($category_id) {
		$ret = array();

    $categoryClass = hikashop_get('class.category');
		$category = $categoryClass->get($category_id);
		if(empty($category))
			return $ret;

    if (!empty(plgHikashopBFcategoryImages::$_categories)) {
      switch(plgHikashopBFcategoryImages::$_categorymode) {
        case 'E':
          if (in_array($category_id, plgHikashopBFcategoryImages::$_categories)) {
            return $ret;
          }
          break;
        case 'I':
          if (!in_array($category_id, plgHikashopBFcategoryImages::$_categories)) {
            return $ret;
          }
          break;
      }
    }

		$db = JFactory::getDBO();
		$query = 'SELECT product.product_id '.
			' FROM '.hikashop_table('product').' AS product '.
			' INNER JOIN '.hikashop_table('product_category').' AS product_category ON (product.product_id = product_category.product_id) OR (product.product_parent_id = product_category.product_id) '.
			' INNER JOIN '.hikashop_table('category').' AS category ON category.category_id = product_category.category_id '.
			' WHERE category.category_published = 1' . 
			' AND product.product_published = 1' . 
			' AND NOT EXISTS ( '.
          'SELECT 1'.
          ' FROM '.hikashop_table('product').' AS product1'.
          ' WHERE product1.product_parent_id = product.product_id'.
                     ' )'. 
			' AND category.category_left >= ' . (int)$category->category_left . 
      ' AND category.category_right <= ' . (int)$category->category_right;

    if (!empty(plgHikashopBFcategoryImages::$_products)) {
      switch(plgHikashopBFcategoryImages::$_productmode) {
        case 'E':
          $query .= " AND product.product_id NOT IN (' " . implode("','", plgHikashopBFcategoryImages::$_products) . "' )";
          $query .= " AND product.product_parent_id NOT IN (' " . implode("','", plgHikashopBFcategoryImages::$_products) . "' )";
          break;
        case 'I':
          $query .= " AND ( product.product_id IN (' " . implode("','", plgHikashopBFcategoryImages::$_products) . "' )";
          $query .= "    OR product.product_parent_id IN (' " . implode("','", plgHikashopBFcategoryImages::$_products) . "' ) )";
          break;
      }
    }

		$db->setQuery($query);
		if(!HIKASHOP_J25)
			$category_products = $db->loadResultArray();
		else
			$category_products = $db->loadColumn();
      
    if (empty($category_products)) {
      return $ret;
    }

		$query = 'SELECT DISTINCT file_path  ' .
             'FROM '.hikashop_table('file').' ' .
             'WHERE file_ref_id IN ('.implode(',',$category_products).') ' .
             'AND file_type = \'product\' ' .
             'AND file_ordering = 0 ' .
             'ORDER BY RAND()';
		$db->setQuery($query);
		$images = $db->loadColumn();
    return $images;
	}
}
