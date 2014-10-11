<?php

/**

 * Created by JetBrains PhpStorm.

 * User: Alesioo

 * Date: 12.12.12

 * Time: 16:24

 * To change this template use File | Settings | File Templates.

 */

function loo_min ($s)

{

    return $s->price;

}



class Etheme_Buyshopconfig_Helper_Data extends Mage_Core_Helper_Abstract

{



    public function fileLoad($name, &$formData, $pathModule)

    {

        if (isset($_FILES[$name]['name']) && $_FILES[$name]['name'] != null) {

            $fileUploader = new Varien_File_Uploader($name);

            $fileUploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));

            $fileUploader->setAllowRenameFiles(false);

            $fileUploader->setFilesDispersion(false);

            //$path = Mage::getBaseDir() . DS.$pathModule.DS ;

            $path = 'skin' . DS . 'adminhtml' . DS . 'base' . DS . 'default' . DS . 'buyshop' . DS . 'images' . DS . 'Configsets' . DS;

            $fileUploader->save($path, $_FILES[$name]['name']);

            $formData[$name] = $pathModule . DS . $_FILES[$name]['name'];

        }

    }



    //loop through folders and sub folders with option to remove specific files.

    public function listFolderFiles($dir, $exclude)

    {

        $ffs = scandir($dir);

        echo '<ul class="ulli">';

        foreach ($ffs as $ff) {

            if (is_array($exclude) and !in_array($ff, $exclude)

            ) {

                if ($ff != '.' && $ff != '..') {

                    if (!is_dir($dir . '/' . $ff)) {

                        echo '<li><a href="edit_page.php?path=' . ltrim($dir . '/' . $ff, './') . '">' . $ff . '</a>';

                    } else {

                        echo '<li>' . $ff;

                    }

                    if (is_dir($dir . '/' . $ff)) $this->listFolderFiles($dir . '/' . $ff, $exclude);

                    echo '</li>';

                }

            }

        }

        echo '</ul>';

    }





    //Return an array of file names and folders in directory:

    function ReadFolderDirectory($dir = "root_dir/here")

    {

        $listDir = array();

        if ($handler = opendir($dir)) {

            while (($sub = readdir($handler)) !== FALSE) {

                if ($sub != "." && $sub != ".." && $sub != "Thumb.db") {

                    if (is_file($dir . "/" . $sub)) {

                        $listDir[] = $sub;

                    } elseif (is_dir($dir . "/" . $sub)) {

                        $listDir[$sub] = $this->ReadFolderDirectory($dir . "/" . $sub);

                    }

                }

            }

            closedir($handler);

        }

        return $listDir;

    }



    //view files by extension



    public function listFolderFiles_by_ext($dir, $type)

    {

        $dir = '.\\' . $dir . '\\'; // reminder: escape your slashes

        $filetype = "*." . $type;

        $filelist = shell_exec("dir {$dir}{$filetype} /a-d /b");

        $file_arr = explode("\n", $filelist);

        array_pop($file_arr); // last line is always blank

        return $file_arr;

    }





    public function cropResizeImg($fileName, $tmp, $width, $height = '')

    {

        $folderURL = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $imageURL = $folderURL . $fileName;



        $basePath = $tmp;

        $newPath = Mage::getBaseDir() . DS . 'skin' . DS . 'adminhtml' . DS . 'base' . DS . 'default' . DS . 'buyshop' . DS . 'images' . DS . 'Configsets' . DS . $fileName;



        if (is_file($tmp)) {

            $imageObj = new Varien_Image($basePath);

            $imageObj->constrainOnly(TRUE);

            $imageObj->keepAspectRatio(FALSE);

            $imageObj->keepFrame(FALSE);



            $currentRatio = $imageObj->getOriginalWidth() / $imageObj->getOriginalHeight();

            $targetRatio = $width / $height;



            if ($targetRatio > $currentRatio) {

                $imageObj->resize($width, null);

            } else {

                $imageObj->resize(null, $height);

            }



            $diffWidth = $imageObj->getOriginalWidth() - $width;

            $diffHeight = $imageObj->getOriginalHeight() - $height;





            //$imageObj->resize($width, $height);

            $imageObj->crop(

                floor($diffHeight * 0.5),

                floor($diffWidth / 2),

                ceil($diffWidth / 2),

                ceil($diffHeight * 0.5)

            );



            $imageObj->save($newPath);

        }



        return $fileName;

    }









    public function getAllBrandsFromSettings()

    {

        $brands=explode(',',Mage::getStoreConfig('buyshopconfig/options/brands'));

        $manufacturers=array();



        if(count($brands))

        {

            foreach($brands as $brand)

            {

                $manufacturers[]['label']=trim($brand);

            }

        }

        return $manufacturers;

    }







    public function shop_by_brands_html()

    {

        $data = $this->getAllBrandsFromSettings();



        $output = '';





        if (count($data)) {



            foreach ($data as $brand) {

                $output .= "<li class='level0 nav-2 last parent'><a href='".Mage::getURL() . 'catalogsearch/result/?q=' . $brand['label'] . "'><span>".$brand['label']."</span></a>";





            }

        }

        return $output;

    }





    public function getStars($_product)

    {

        /**

         * Getting reviews collection object

         */

        $productId = $_product->getId();

        $reviews = Mage::getModel('review/review')

            ->getResourceCollection()

            ->addStoreFilter(Mage::app()->getStore()->getId())

            ->addEntityFilter('product', $productId)

            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)

            ->setDateOrder()

            ->addRateVotes();

        /**

         * Getting average of ratings/reviews

         */

        $stars = 0;

        $avg = 0;

        $output = '';

        $ratings = array();

        if (count($reviews) > 0) {

            foreach ($reviews->getItems() as $review) {

                foreach ($review->getRatingVotes() as $vote) {

                    $ratings[] = $vote->getPercent();

                }

            }

            $crat=count($ratings);

            if($crat==0)$crat=1;

            $avg = array_sum($ratings) / $crat;

            $stars = round($avg * 5 / 100);

        }





        if ($stars) {

            $output .= '<div class="rating">';

            $output .= '<strong>';

            for ($i = 0; $i < $stars; $i++) $output .= '<a><i class="icon-star"></i></a>';

            $output .= '</strong>';

            for ($i = 0; $i < (5 - $stars); $i++) $output .= '<a><i class="icon-star"></i></a>';

            $output .= '</div>';

        }



        return $output;

    }



    public function getMedia($_product, $widthBig, $heightBig, $widthSmall, $heightSmall, $max = 6)

    {

        $output = '';

        $_media = Mage::getModel('catalog/product')->load($_product->getId())->getMediaGalleryImages();

        if ($_media->count()) {

            $count = 0;

            foreach ($_media as $_image) {

                $count++;

                if ($count <= $max)

                    $output .= '<a class="image" data-rel="' . Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($widthBig, $heightBig) . '" href="' . $_product->getProductUrl() . '"><img class="thumb" alt="'.$_product->getImageLabel().'" title="'.$this->escapeHtml($_product->getImageLabel()).'" src="' . Mage::helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize($widthSmall, $heightSmall) . '"></a>';

            }



        }

        return $output;

    }



    public function getMediaCount($_product)

    {

        return Mage::getModel('catalog/product')->load($_product->getId())->getMediaGalleryImages()->count();

    }









    public function CountMedia($_product)

    {

        return Mage::getModel('catalog/product')->load($_product->getId())->getMediaGalleryImages()->count();

    }



    public function price($_product)

    {

        if ($_product->type_id == 'grouped') {

            $final_price=Mage::helper('tax')->getPrice($_product, $this->prepareGroupedProductPrice($_product));

            return 'From '.Mage::helper('core')->currency($final_price);

        }elseif($_product->type_id == 'bundle')

        {

            $final_price=Mage::helper('tax')->getPrice($_product, $this->prepareBundleProductPrice($_product));

            return 'From '.Mage::helper('core')->currency($final_price);

        }

        else

        {

            $final_price=Mage::helper('tax')->getPrice($_product, $_product->getFinalPrice());

            return Mage::helper('core')->currency($final_price);



        }

    }



    public function prepareGroupedProductPrice($groupedProduct)

    {

        $aProductIds = $groupedProduct->getTypeInstance()->getChildrenIds($groupedProduct->getId());

        $prices = array();

        foreach ($aProductIds as $ids) {

            foreach ($ids as $id) {

                $aProduct = Mage::getModel('catalog/product')->load($id);

                $prices[] = $aProduct->getPriceModel()->getPrice($aProduct);

            }

        }



        sort($prices);

        return $prices[0];

    }





    public function prepareBundleProductPrice($product)

    {



        $optionCol= $product->getTypeInstance(true)

            ->getOptionsCollection($product);

        $selectionCol= $product->getTypeInstance(true)

            ->getSelectionsCollection(

            $product->getTypeInstance(true)->getOptionsIds($product),

            $product

        );

        $optionCol->appendSelections($selectionCol);

        $price = $product->getPrice();



        foreach ($optionCol as $option) {

            if($option->required) {

                $selections = $option->getSelections();



                //	echo "line 345";

                $minPrice = min( array_map( "loo_min", $selections) );

                //	echo "line 347";



                if($product->getSpecialPrice() > 0) {

                    $minPrice *= $product->getSpecialPrice()/100;

                }



                $price += round($minPrice,2);

            }

        }

        return $price;



    }



    public function priceOld($_product)

    {

        $final_price=Mage::helper('tax')->getPrice($_product, $_product->getPrice());

        return Mage::helper('core')->currency($final_price);

    }



    public function addToCartLink($_product, $el, $in_list_mode = false)

    {



        $IE8 = false;

        if (preg_match('/(?i)msie [1-8]/', $_SERVER['HTTP_USER_AGENT'])) $IE8 = true;

        if (Mage::getStoreConfig('buyshopconfig/options/catalog_mode')) return;

        $output = '';
 
        /* Sashas_Callfor_Price */    
        $callforprice=Mage::app()->getLayout()->createBlock('callforprice/listcart')->setProduct($_product)->toHtml();                
        if (!trim($callforprice))
        	return  $output;                       	 
        /* Sashas_Callfor_Price */
 
        if (!$in_list_mode) $output .= '<div class="product-tocart">';	
        
        if ($_product->isSaleable()) {

            if (Mage::getStoreConfig('buyshopconfig/options/ajax_add_to_cart') && !$IE8) {

                if (!($_product->getTypeInstance(true)->hasRequiredOptions($_product) || $_product->isGrouped())) {
                	
                 
 
                	          	
						
                    if (!$in_list_mode) $output .= '<a onclick="setLocationAjax(\'' . $el->getAddToCartUrl($_product) . '\',\'' . $_product->getId() . '\')"><i class="icon-basket"></i></a>';

                    else $output .= '<a onclick="setLocationAjax(\'' . $el->getAddToCartUrl($_product) . '\',\'' . $_product->getId() . '\')" class="button btn-cart"><i class="icon-basket"></i>' . $el->__('Add to Cart') . '</a>';
                	 
 

                } else {

                    if (!$in_list_mode) $output .= '<a onclick="showOptions(\'' . $_product->getId() . '\')"><i class="icon-basket"></i></a>';

                    else $output .= '<a onclick="showOptions(\'' . $_product->getId() . '\')" class="button btn-cart"><i class="icon-basket"></i>' . $el->__('Add to Cart') . '</a>';

                    $output .= '<a href=\'' . $el->getUrl('ajax/index/options', array('product_id' => $_product->getId())) . '\'  class="fancybox popup_fancybox' . $_product->getId() . ' hidden" ></a>';

                }

            } else {

                if (!$in_list_mode) $output .= '<a onclick="location.href=\'' . $el->getAddToCartUrl($_product) . '\'"><i class="icon-basket"></i></a>';

                else $output .= '<a  onclick="location.href=\'' . $el->getAddToCartUrl($_product) . '\'" class="button btn-cart"><i class="icon-basket"></i>' . $el->__('Add to Cart') . '</a>';

            }

        } else {

            if(Mage::getStoreConfig('buyshoplayout/product_listing/product_listing_image_size')!='small')

                $output .= '<a class="outofstock">' . $el->__('Out of stock') . '</a>';

        }

        if (!$in_list_mode) $output .= '</div>';

        return $output;

    }





    public function addWishCompLink($_product, $el, $in_product = false)

    {

        $output = '';



        $IE8 = false;

        if (preg_match('/(?i)msie [1-8]/', $_SERVER['HTTP_USER_AGENT'])) $IE8 = true;



        if (!$in_product) $output .= '<div class="clearfix"></div><div class="product-link">';



        if (Mage::helper('wishlist')->isAllow()) {

            if (Mage::getStoreConfig('buyshopconfig/options/ajax_wish_comp') && !$IE8) {

                if (!$in_product) {

                    $output .= '<a  href="#"  onclick="ajaxWishlist(\'' . $el->helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false)) . '\',' . $_product->getId() . ');return false;">' . $el->__('Add to Wishlist') . '</a><br>';

                } else {

                    $output .= '<li><a href="#"  onclick="ajaxWishlist(\'' . $el->helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false)) . '\',' . $_product->getId() . ');return false;" class="small_icon_color"><i class="icon-heart"></i></a><a href="#"  onclick="ajaxWishlist(\'' . $el->helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false)) . '\',' . $_product->getId() . ');return false;">' . $el->__('Add to Wishlist') . '</a></li>';

                }

                if ($_compareUrl = $el->getAddToCompareUrl($_product)) {

                    if (!$in_product) {

                        $output .= '<a href="#" onclick="ajaxCompare(\'' . $_compareUrl . '\',' . $_product->getId() . ');return false;">' . $el->__('Add to Compare') . '</a>';

                    } else {

                        $output .= '<li><a href="#" onclick="ajaxCompare(\'' . $_compareUrl . '\',' . $_product->getId() . ');return false;" class="small_icon_color"><i class="icon-popup"></i></a><a href="#" onclick="ajaxCompare(\'' . $_compareUrl . '\',' . $_product->getId() . ');return false;">' . $el->__('Add to Compare') . '</a></li>';

                    }



                }

            }else{

                if ($in_product) {

                    $output .= '<li><a href="' . Mage::helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false))  . '" class="small_icon_color"><i class="icon-heart"></i></a><a href="' . Mage::helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false)) . '">' . $el->__('Add to Wishlist') . '</a></li>';

                    $output .= '<li><a href="' . Mage::helper('catalog/product_compare')->getAddUrl($_product) . '" class="small_icon_color"><i class="icon-popup"></i></a><a href="' . Mage::helper('catalog/product_compare')->getAddUrl($_product) . '">' . $el->__('Add to Compare') . '</a></li>';

                } else {

                    $output .= '<a href="' . Mage::helper('wishlist')->getAddUrlWithParams($_product,array('_secure'=>false))  . '">' . $el->__('Add to Wishlist') . '</a><br>';

                    $output .= '<a href="' . Mage::helper('catalog/product_compare')->getAddUrl($_product) . '">' . $el->__('Add to Compare') . '</a>';

                }

            }

        }



        if (!$in_product) $output .= '</div>';

        return $output;

    }





    public function getProductHover($_product, $el, $widthBig, $heightBig, $widthSmall, $heightSmall, $type = '',$show_short_description=true,$show_short_attributes=true,$price)

    {

        $output = '';

        $url=$_product->getProductUrl();

        //$_product=Mage::getModel('catalog/product')->load($_product->getId());



        $now = date("Y-m-d");

        $specialFrom = substr($_product->getData('special_from_date'), 0, 10);

        $specialTo = substr($_product->getData('special_to_date'), 0, 10);





        if (!empty($specialFrom) && !empty($specialTo)) {

            if ($now >= $specialFrom && $now <= $specialTo) $special = true;



        } elseif (!empty($specialFrom) && empty($specialTo)) {

            if ($now >= $specialFrom) $special = true;



        } elseif (empty($specialFrom) && !empty($specialTo)) {

            if ($now <= $specialTo) $special = true;

        }



        if (Mage::getStoreConfig('buyshopconfig/options/image_rollover_mode')) {

            $short_description=$_product->getShortDescription();

            if($show_short_description && !empty($short_description) && $type!='small' && Mage::getStoreConfig('buyshopconfig/options/short_description'))$short_description='<div class="short">'.$short_description.'</div>';

            else $short_description='';



            $short_attributes=$_product->getShortparams();



            if($show_short_attributes && !empty($short_attributes) && $type!='small' && Mage::getStoreConfig('buyshopconfig/options/short_attributes'))$short_attributes='<div class="short">'.$short_attributes.'</div>';

            else $short_attributes='';



            $output .= '<!--PRODUCT HOVER-->';

            $output .= '<div class="preview ' . $type . ' hidden-tablet hidden-phone">

            <div class="wrapper">';

            $media_exist='';

            if ($this->CountMedia($_product)) {

                $media_exist='with_media';

                $output .= '<div class="col-1">

                        ' . $this->getMedia($_product, $widthBig, $heightBig, $widthSmall, $heightSmall) . '

                </div>';

            }



            $output .= ' <div class="col-2 '.$media_exist.'">

                          <div class="big_image"><a href="' . $url . '"><img data-rel="' . $el->helper('catalog/image')->init($_product, 'small_image')->resize($widthBig, $heightBig) . '" src="' . $el->helper('catalog/image')->init($_product, 'small_image')->resize($widthBig, $heightBig) . '" alt="' . $el->stripTags($_product->getName(), null, true) . '" /></a></div>

                          ' . $this->getProductLabel($_product,true,$el) . '

                          ' . $this->QuickView($_product,$el);
                          
                          // Custom Stock Status
			$output.=Mage::helper('customstockstatus')->getListStatus($_product->getId());
        // End Custom Stock Status

            if(!Mage::getStoreConfig('buyshopconfig/options/catalog_mode'))

            {

                $output .= '<div class="wrapper-hover">

                                <h3 class="product-name"><a href="' . $url . '">' . $el->stripTags($_product->getName(), null, true) . '</a></h3>

                                <div class="wrapper">

                                     ' . $price;



                if(isset($special))

                    if ($special){

                        $output.=$this->outputDiscountLabel($_product);

                    }



                $output .=  $this->addToCartLink($_product, $el) . '

                                </div>

                               ' . $this->addWishCompLink($_product, $el) . '

                               ' . $this->getStars($_product) . '

                               ' . $short_attributes . '

                               ' . $short_description . '

                              </div>';



            } else{

                $output .= ' <div class="wrapper-hover">

                                <div class="product-name">

                                    <a href="' . $url . '">' . $el->stripTags($_product->getName(), null, true) . '</a>

                                 </div>

                             </div>';



            }





            $output .= '  </div>

                       </div>

              </div>';

            $output .= '<!--PRODUCT HOVER EOF-->';

        }

        return $output;

    }





    public function getProductHtml($_product, $el, $widthBig, $heightBig, $span = 2, $special = false, $list_mode = false, $disable_info = false, $grid = true,$price)

    {

        $output = '';

        $description = '';



        $now = date("Y-m-d");

        $specialFrom = substr($_product->getData('special_from_date'), 0, 10);

        $specialTo = substr($_product->getData('special_to_date'), 0, 10);



        $url=$_product->getProductUrl();

        //$_product=Mage::getModel('catalog/product')->load($_product->getId());



        if (!empty($specialFrom) && !empty($specialTo)) {

            if ($now >= $specialFrom && $now <= $specialTo) $special = true;



        } elseif (!empty($specialFrom) && empty($specialTo)) {

            if ($now >= $specialFrom) $special = true;



        } elseif (empty($specialFrom) && !empty($specialTo)) {

            if ($now <= $specialTo) $special = true;

        }



        $is_hover = (Mage::getStoreConfig('buyshopconfig/options/image_rollover_mode')) ? 'onhover' : 'nohover';

        if (!$grid) $is_hover = 'nohover';

        $output .= '<div class="span' . $span . ' product">

                        <div class="product-image-wrapper ' . $is_hover . '">

                            ' . $this->getProductLabel($_product, $list_mode,$el) . '

                            ' . $this->QuickView($_product,$el) . '

                            <a href="' . $url . '"><img  src="' . $el->helper('catalog/image')->init($_product, 'small_image')->resize($widthBig, $heightBig) . '" class="product-retina" data-image2x="' . $el->helper('catalog/image')->init($_product, 'small_image')->resize($widthBig * 2, $heightBig * 2) . '" width="' . $widthBig . '" height="' . $heightBig . '"  alt="' . $el->stripTags($_product->getName(), null, true) . '"></a>

        </div>';



        if($span!=2)$output.=$this->countdownSpecialPrice($_product,'defaultCountdown',$el);
        
        // Custom Stock Status
			$output.=Mage::helper('customstockstatus')->getListStatus($_product->getId());
        // End Custom Stock Status









        if(!Mage::getStoreConfig('buyshopconfig/options/catalog_mode'))

        {

            $description .= ' <div class="wrapper-hover">

                                <h3 class="product-name"><a href="' . $url . '">' . $el->stripTags($_product->getName(), null, true) . '</a></h3>

                                <div class="wrapper">';



            if ($special) {

                /*$description .= '<div class="product-price-regular">

                                        <div class="product-price-regular">

                                            <span class="spec">' . $this->price($_product) . '</span>

                                            <span class="old">' . $this->priceOld($_product) . '</span>

                                        </div>

                                </div>';*/

                $description.=$price;

                $description.=$this->outputDiscountLabel($_product);

            } else {

                //$description .= '<div class="product-price">' . $this->price($_product) . '</div>';

                $description .=$price;



            }

            $description .= $this->addToCartLink($_product, $el) . '

                                </div>

                            </div>';

        }else{

            $description .= ' <div class="wrapper-hover">

                                <h3 class="product-name tcenter">

                                    <a href="' . $url . '">' . $el->stripTags($_product->getName(), null, true) . '</a>

                                 </h3>

                               ';



            $description .= '

                            </div>';

        }





        if ($disable_info) $description = '';

        if (!$list_mode) {

            $output .= $description;

        } else {

            if (Mage::getStoreConfig('buyshoplayout/product_listing/product_listing_mode') == 'usual') $output .= $description;

        }





        $output .= '</div>';



        return $output;

    }



    function nicetime($date)

    {

        if (empty($date)) {

            return "No date provided";

        }



        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");

        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");



        $now = time();

        $unix_date = strtotime($date);



        // check validity of date

        if (empty($unix_date)) {

            return "Bad date";

        }



        // is it future date or past date

        if ($now > $unix_date) {

            $difference = $now - $unix_date;

            $tense = "ago";



        } else {

            $difference = $unix_date - $now;

            $tense = "from now";

        }



        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {

            $difference /= $lengths[$j];

        }



        $difference = round($difference);



        if ($difference != 1) {

            $periods[$j] .= "s";

        }



        return "$difference $periods[$j] {$tense}";

    }



    function hex2rgb($hex)

    {

        $hex = str_replace("#", "", $hex);



        if (strlen($hex) == 3) {

            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));

            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));

            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));

        } else {

            $r = hexdec(substr($hex, 0, 2));

            $g = hexdec(substr($hex, 2, 2));

            $b = hexdec(substr($hex, 4, 2));

        }

        $rgb = array($r, $g, $b);

        //return implode(",", $rgb); // returns the rgb values separated by commas

        return $rgb; // returns an array with the rgb values

    }



    function countdownSpecialPrice($_product,$selector,$el)

    {

        $output='';

        $specialTo = substr($_product->getData('special_to_date'), 0, 10);

        $now = date("Y-m-d");

        if (!empty($specialTo) && Mage::getStoreConfig('buyshopconfig/options/countdown')) {

            if ($now < $specialTo)

            {

                $to_year=substr($_product->getData('special_to_date'), 0, 4);

                $to_month=substr($_product->getData('special_to_date'), 5, 2);

                $to_day=substr($_product->getData('special_to_date'), 8, 2);



                $output.='<div class="countdown_box">

                            <div class="countdown_inner">

                                <div class="title">'.$el->__('This limited  offer ends in').':</div>

                                <script type="text/javascript">

                                jQuery(function () {

                                    jQuery(".'.$selector.'-'.$_product->getId().'").countdown({until: new Date('.$to_year.','.($to_month-1).', '.$to_day.')});

                                });

                               </script>

                                <div class="'.$selector.'-'.$_product->getId().' hasCountdown"></div>

                            </div>

                        </div>';

            }

        }

        return $output;

    }



    function getProductLabel($_product, $list_mode,$el)

    {

        if (!$list_mode) return;

        $output = '';

        if (Mage::getStoreConfig('buyshopconfig/product_labels/show_sale_label')) {



            $now = date("Y-m-d");

            $specialFrom = substr($_product->getData('special_from_date'), 0, 10);

            $specialTo = substr($_product->getData('special_to_date'), 0, 10);



            $special = false;



            if (!empty($specialFrom) && !empty($specialTo)) {

                if ($now >= $specialFrom && $now <= $specialTo) $special = true;



            } elseif (!empty($specialFrom) && empty($specialTo)) {

                if ($now >= $specialFrom) $special = true;



            } elseif (empty($specialFrom) && !empty($specialTo)) {

                if ($now <= $specialTo) $special = true;

            }

            if ($special) $output .= '<div class="label_sale_' . Mage::getStoreConfig('buyshopconfig/product_labels/sale_label_position') . '">'.$el->__('Sale').'</div>';



        }



        if (Mage::getStoreConfig('buyshopconfig/product_labels/show_new_label')) {

            $now = date("Y-m-d");

            $newsFrom = substr($_product->getData('news_from_date'), 0, 10);

            $newsTo = substr($_product->getData('news_to_date'), 0, 10);



            $new = false;



            if (!empty($newsFrom) && !empty($newsTo)) {

                if ($now >= $newsFrom && $now <= $newsTo) $new = true;



            } elseif (!empty($newsFrom) && empty($newsTo)) {

                if ($now >= $newsFrom) $new = true;



            } elseif (empty($newsFrom) && !empty($newsTo)) {

                if ($now <= $newsTo) $new = true;

            }



            if ($new) $output .= '<div class="label_new_' . Mage::getStoreConfig('buyshopconfig/product_labels/new_label_position') . '">'.$el->__('New').'</div>';



        }

        return $output;

    }



    public function countBestsellers()

    {

        $storeId = Mage::app()->getStore()->getId();



        $products = Mage::getResourceModel('reports/product_collection')

            ->addOrderedQty()

        //->addAttributeToSelect('*')

            ->addAttributeToSelect(array('name', 'price', 'small_image', 'short_description', 'description')) //edit to suit tastes

            ->addAttributeToFilter('status', 1)

            ->setStoreId($storeId)

            ->addStoreFilter($storeId)

            ->setOrder('ordered_qty', 'desc'); //best sellers on top



        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);

        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);



        return count($products);

    }



    public function outputDiscountLabel($_product)

    {



        if (!($_product->type_id == 'grouped' || $_product->type_id == 'bundle')) {



            if(!Mage::getStoreConfig('buyshopconfig/product_labels/discount_label'))return;

            if ($_product->type_id != 'grouped')

                $price_new = $_product->getFinalPrice();

            else

                $price_new = $_product->min_price;



            $price_old = $_product->getPrice();



            if($price_old==0)$price_old=1;

            $discount=round((($price_new-$price_old)*100)/$price_old);





            return '<div class="sale_discount img-rounded">'.$discount.'%</div>';

        } else return '';





    }



    public function QuickView($_product,$el)

    {

        $text=$el->__('Quick View');

        if(!Mage::getStoreConfig('buyshopconfig/options/quick_view'))return;

        return '

            <a  onclick="showOptions(\'' . $_product->getId() . '\')" class="quickview img-circle hidden-phone hidden-small-desktop hidden-tablet">'.$text.'</a>

            <a href=\'' . $el->getUrl('ajax/index/options', array('product_id' => $_product->getId())) . '\'   class="fancybox popup_fancybox' . $_product->getId() . ' hidden"></a>

        ';

    }



    function replace_uri($str) {

        $pattern = '#(^|[^\"=]{1})(http://|ftp://|mailto:|news:)([^\s<>]+)([\s\n<>]|$)#sm';

        return preg_replace($pattern,"\\1<a target=\"_blank\" href=\"\\2\\3\">\\2\\3</a>\\4",$str);

    }





    function refreshCssFiles($store, $website)

    {

        /*3 ways to refresh CSS

         * DEFAULT-WEBSITES-STORES  (All available stores)

         * WEBSITES-STORES (stores from choosen website)

         * STORE (choosen store)

         * */

        if(!$website)

        {

            //refresh all Websites css

            foreach(Mage::app()->getWebsites() as $website)

            {

                $this->refreshWebsiteStores($website);

            }

        }

        else

        {

            if($store)

            {

                //refresh Store css

                $this->refreshStoreCss($store);

            }

            else

            {

                //refresh Website css

                $this->refreshWebsiteStores($website);

            }

        }

    }



    function refreshStoreCss($store)

    {

        Mage::register('store_for_css', $store);

        $path = Mage::getBaseDir() . '/' . 'skin/frontend/default/buyshop/css/colors/';



        $prefix = 'colors_';

        if ($store) {

            $filename = 'test'.$store;

        }



        $path_full = $path . $prefix . $filename . '.css';

        /*

         * how get frontend phtml output http://stackoverflow.com/questions/12290938/get-frontend-phtml-templates-output-inside-a-model-method-in-magento

         * */

        $css_output = Mage::app()->getLayout()->createBlock('core/template')->setData('area', 'frontend')->setTemplate('etheme/buyshop/cssrefresh/colors.phtml')->toHtml();



        /*

         * write to file described here  http://inchoo.net/ecommerce/magento/magento-code-library/

         * */

        try {

            if(file_exists($path_full))unlink($path_full);

            $flocal = new Varien_Io_File();

            $flocal->open(array('path' => $path));

            $flocal->streamOpen($path_full, 'w+');

            $flocal->streamWrite($css_output);

            $flocal->streamClose();

            Mage::getSingleton('adminhtml/session')->addSuccess('CSS file '.$prefix.$store.'.css was refreshed successfully.');

        } catch (Exception $e) {

            echo $e->getMessage();

        }

        Mage::unregister('store_for_css');

    }



    function refreshWebsiteStores($website)

    {

        foreach(Mage::app()->getWebsite($website)->getStoreCodes() as $store)$this->refreshStoreCss($store);

    }

}