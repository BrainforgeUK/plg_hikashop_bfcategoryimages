<?php
/**
 * @package   Brainforge Category Image Slideshow for Hikashop.
 * @version   0.0.1
 * @author    http://www.brainforge.co.uk
 * @copyright Copyright (C) 2016 Jonathan Brain. All rights reserved.
 * @license	 GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormFieldBFHikashopNamebox extends JFormField
{
  /**
   */
  protected $type = 'bfhikashopnamebox';
  /**
   */
  protected function getInput()
  {
    if (!isset($this->delete) || (string)@$this->delete == '1' || (string)@$this->delete == 'true') {
      $this->delete = true;
    }
    else {
      $this->delete = false;
    }

    if (!isset($this->default_text)) {
      $this->default_text = 'HIKA_NONE';
    }
    
    if(!include_once(rtrim(JPATH_ADMINISTRATOR,'/').'/components/com_hikashop/helpers/helper.php')) return true;

    $nameboxType = hikashop_get('type.namebox');
    $html = $nameboxType->display(
      $this->name,
      @$this->value,
      hikashopNameboxType::NAMEBOX_MULTIPLE,
      $this->getAttribute('target'),
      array(
        'delete' => $this->delete,
        'default_text' => '<em>'.JText::_($this->default_text).'</em>',
      )
    );
    return $html;
  }
}
