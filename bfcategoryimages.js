/**
 * @copyright	Copyright (C) 2016 brainforge (www.brainforge.co.uk). All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

function BFCIfadeoutAll(imageCount, catid) {
  for (i=1; i<imageCount; i++) {
    jQuery("#hikashop_category_image_for_"+catid+"_"+i).fadeOut(0);
  }
}

function BFCIswapImage(index, imageCount, catid, fadeouttime, fadeintime) {
  jQuery("#hikashop_category_image_for_"+catid+"_"+index).fadeOut(fadeouttime);
  index += 1;
  if (index >= imageCount) index = 0;
  jQuery("#hikashop_category_image_for_"+catid+"_"+index).fadeIn(fadeintime);
  return index;
}