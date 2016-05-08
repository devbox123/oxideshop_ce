<?php
class PicturesManager
{
    /**
     * @var oxConfig
     */
    private $config;
    /**
     * @var oxPictureHandler
     */
    private $pictureHandler;

    public function __construct(oxConfig $config)
    {
        $this->config = $config;
        $this->pictureHandler = oxNew('oxPictureHandler');
    }

    /**
     * collect article pics, icons, zoompic and puts it all in an array
     * structure of array (ActPicID, ActPic, MorePics, Pics, Icons, ZoomPic)
     *
     * @return array
     */
    public function getPictureGallery(oxarticle $article)
    {
        //initialize
        $blMorePic = false;
        $aArtPics = array();
        $aArtIcons = array();
        $iActPicId = 1;
        $sActPic = $this->getPictureUrl($iActPicId, $article);

        $oStr = getStr();
        $iCntr = 0;
        $iPicCount = $this->config->getConfigParam('iPicCount');
        $blCheckActivePicId = true;

        for ($i = 1; $i <= $iPicCount; $i++) {
            $sPicVal = $this->getPictureUrl($i, $article);
            $sIcoVal = $this->getIconUrl($i, $article);
            if (!$oStr->strstr($sIcoVal, 'nopic_ico.jpg') && !$oStr->strstr($sIcoVal, 'nopic.jpg') &&
                !$oStr->strstr($sPicVal, 'nopic_ico.jpg') && !$oStr->strstr($sPicVal, 'nopic.jpg')
            ) {
                if ($iCntr) {
                    $blMorePic = true;
                }
                $aArtIcons[$i] = $sIcoVal;
                $aArtPics[$i] = $sPicVal;
                $iCntr++;

                if ($iActPicId == $i) {
                    $sActPic = $sPicVal;
                    $blCheckActivePicId = false;
                }
            } elseif ($blCheckActivePicId && $iActPicId <= $i) {
                // if picture is empty, setting active pic id to next
                // picture
                $iActPicId++;
            }
        }

        $blZoomPic = false;
        $aZoomPics = array();
        $iZoomPicCount = $this->config->getConfigParam('iPicCount');

        for ($j = 1, $c = 1; $j <= $iZoomPicCount; $j++) {
            $sVal = $this->getZoomPictureUrl($j, $article);

            if ($sVal && !$oStr->strstr($sVal, 'nopic.jpg')) {
                $blZoomPic = true;
                $aZoomPics[$c]['id'] = $c;
                $aZoomPics[$c]['file'] = $sVal;
                //anything is better than empty name, because <img src=""> calls shop once more = x2 SLOW.
                if (!$sVal) {
                    $aZoomPics[$c]['file'] = 'nopic.jpg';
                }
                $c++;
            }
        }

        $aPicGallery = array(
            'ActPicID' => $iActPicId,
            'ActPic'   => $sActPic,
            'MorePics' => $blMorePic,
            'Pics'     => $aArtPics,
            'Icons'    => $aArtIcons,
            'ZoomPic'  => $blZoomPic,
            'ZoomPics' => $aZoomPics
        );

        return $aPicGallery;
    }

    /**
     * Returns article picture
     *
     * @param int $iIndex picture index
     *
     * @return string
     */
    public function getPictureUrl($iIndex = 1, oxArticle $article)
    {
        if ($iIndex) {
            $sImgName = false;
            if (!$article->isFieldEmpty("oxarticles__oxpic" . $iIndex)) {
                $sImgName = basename($article->{"oxarticles__oxpic$iIndex"}->value);
            }

            $sSize = $this->config->getConfigParam('aDetailImageSizes');

            return $this->pictureHandler
                ->getProductPicUrl("product/{$iIndex}/", $sImgName, $sSize, 'oxpic' . $iIndex);
        }
    }

    /**
     * Returns article icon picture url. If no index specified, will
     * return main icon url.
     *
     * @param int $iIndex picture index
     *
     * @return string
     */
    public function getIconUrl($iIndex = 0, oxArticle $article)
    {
        $sImgName = false;
        $sDirname = "product/1/";
        if ($iIndex && !$article->isFieldEmpty("oxarticles__oxpic{$iIndex}")) {
            $sImgName = basename($article->{"oxarticles__oxpic$iIndex"}->value);
            $sDirname = "product/{$iIndex}/";
        } elseif (!$article->isFieldEmpty("oxarticles__oxicon")) {
            $sImgName = basename($article->oxarticles__oxicon->value);
            $sDirname = "product/icon/";
        } elseif (!$article->isFieldEmpty("oxarticles__oxpic1")) {
            $sImgName = basename($article->oxarticles__oxpic1->value);
        }

        $sSize = $this->config->getConfigParam('sIconsize');

        $sIconUrl = $this->pictureHandler->getProductPicUrl($sDirname, $sImgName, $sSize, $iIndex);

        return $sIconUrl;
    }

    /**
     * Returns article thumbnail picture url
     *
     * @param bool $bSsl to force SSL
     *
     * @return string
     */
    public function getThumbnailUrl($bSsl = null, oxArticle $article)
    {
        $sImgName = false;
        $sDirname = "product/1/";
        if (!$article->isFieldEmpty("oxarticles__oxthumb")) {
            $sImgName = basename($article->oxarticles__oxthumb->value);
            $sDirname = "product/thumb/";
        } elseif (!$article->isFieldEmpty("oxarticles__oxpic1")) {
            $sImgName = basename($article->oxarticles__oxpic1->value);
        }

        $sSize = $this->config->getConfigParam('sThumbnailsize');

        return $this->pictureHandler->getProductPicUrl($sDirname, $sImgName, $sSize, 0, $bSsl);
    }

    /**
     * Returns article zoom picture url
     *
     * @param int $iIndex picture index
     *
     * @return string
     */
    public function getZoomPictureUrl($iIndex = '', oxArticle $article)
    {
        $iIndex = (int) $iIndex;
        if ($iIndex > 0 && !$article->isFieldEmpty("oxarticles__oxpic" . $iIndex)) {
            $sImgName = basename($article->{"oxarticles__oxpic" . $iIndex}->value);
            $sSize = $this->config->getConfigParam("sZoomImageSize");

            return $this->pictureHandler->getProductPicUrl(
                "product/{$iIndex}/",
                $sImgName,
                $sSize,
                'oxpic' . $iIndex
            );
        }
    }

    /**
     * Get master zoom picture url
     *
     * @param int $iIndex picture index
     *
     * @return string
     */
    public function getMasterZoomPictureUrl($iIndex, oxArticle $article)
    {
        $sPicUrl = false;
        $sPicName = basename($article->{"oxarticles__oxpic" . $iIndex}->value);

        if ($sPicName && $sPicName != "nopic.jpg") {
            $sPicUrl = $this->config->getPictureUrl("master/product/" . $iIndex . "/" . $sPicName);
            if (!$sPicUrl || basename($sPicUrl) == "nopic.jpg") {
                $sPicUrl = false;
            }
        }

        return $sPicUrl;
    }
}