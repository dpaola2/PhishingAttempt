<?php

/******************************************************************************
 *
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.X
 *	LICENSE: FL0M2SG681CQ - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Pets Classifieds
 *	DOMAIN: pupsnshop.com
 *	FILE: RLQRCODE.CLASS.PHP
 *
 *	The software is a commercial product delivered under single, non-exclusive, 
 *	non-transferable license for one domain or IP address. Therefore distribution, 
 *	sale or transfer of the file in whole or in part without permission of Flynax 
 *	respective owners is considered to be illegal and breach of Flynax License End 
 *	User Agreement. 
 *
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *
 *	Flynax Classifieds Software 2014 |  All copyrights reserved. 
 *
 *	http://www.flynax.com/
 *
 ******************************************************************************/

include dirname(__FILE__) . RL_DS . "phpqrcode" . RL_DS . "qrlib.php";

class rlQrCode extends reefless
{
    private $_sData = "";

    public function generateQR_Code($id = false, $user_id = false)
    {
        global $reefless, $rlDb, $config;

        $reefless->loadClass('Valid');
        $reefless->loadClass('Listings');
        $reefless->loadClass('ListingTypes');
        $reefless->loadClass('Account');

        if (!is_dir(RL_FILES . 'qrcode')) {
            $reefless->rlMkdir(RL_FILES . 'qrcode');
        }

        $sql = "SELECT `T1`.*, `T2`.`Path`, `T2`.`Type` AS `Listing_type`, `T2`.`Key` AS `Cat_key`, `T2`.`Type` AS `Cat_type`, ";
        $sql .= "CONCAT('categories+name+', `T2`.`Key`) AS `Category_pName`, `T2`.`Path` as `Category_Path` ";
        $sql .= "FROM `" . RL_DBPREFIX . "listings` AS `T1` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "categories` AS `T2` ON `T1`.`Category_ID` = `T2`.`ID` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "listing_plans` AS `T3` ON `T1`.`Plan_ID` = `T3`.`ID` ";
        $sql .= "LEFT JOIN `" . RL_DBPREFIX . "accounts` AS `T5` ON `T1`.`Account_ID` = `T5`.`ID` ";
        $sql .= "WHERE `T5`.`Status` = 'active' ";
        if ((int)$id > 0) {
            $sql .= 'AND `T1`.`ID` = ' . $id . ' ';
        }
        if ((int)$user_id > 0) {
            $sql .= 'AND `T1`.`Account_ID` = ' . $user_id . ' ';
        }
        $listings = $rlDb->getAll($sql);

        foreach ($listings as $listing) {
            $data = array('ID' => $listing['ID'], 'Account_ID' => $listing['Account_ID'], 'Filepath' => '', 'Link' => '', 'Title' => '', 'Name' => '', 'Phone' => '');

            /* get account info */
            $seller = $GLOBALS['rlAccount']->getProfile((int)$listing['Account_ID']);
            $data['Name'] = trim($seller['Full_name']);
            $data['Phone'] = $this->getPhone($seller);
            $data['Mail'] = trim($seller['Mail']);

            /* define listing type */
            $listing_type = $GLOBALS['rlListingTypes']->types[$listing['Listing_type']];

            $pagePath = $rlDb->getOne('Path', "`Key` = '" . $listing_type['Page_key'] . "'", 'pages');

            /* get listing title */
            $data['Title'] = $GLOBALS['rlListings']->getListingTitle($listing['Category_ID'], $listing, $listing_type['Key']);

            /* listing link */
            $data['Link'] = RL_URL_HOME;
            $data['Link'] .= $config['mod_rewrite'] ? $pagePath . '/' . $listing['Category_Path'] . '/' . $GLOBALS['rlValid']->str2path($data['Title']) . '-' . $listing['ID'] . '.html' : '?page=' . $pagePath . '&amp;id=' . $listing['ID'];

            $this->finish($data);
        }
    }

    private function getPhone($seller){
        global $config;
        if(isset($config['qrCode_phone_field_name']) && !empty($config['qrCode_phone_field_name'])){
            $fieldName = $config['qrCode_phone_field_name'];
            if(isset($seller['Fields'][$fieldName])){
                return $seller['Fields'][$fieldName]['value'];
            }
        }
        return false;
    }

    /**
     * Remove folder by user ID
     **/
    public function remove_QR_ByUserID($user_id)
    {
        // delete qrcode folder
        $this -> deleteDirectory(RL_FILES .'qrcode'. RL_DS . 'user_' . $user_id);
    }

    /**
     * Remove folder by listing ID
     **/
    public function remove_QR_ByListing($user_id, $listing_id)
    {
        // delete qrcode folder
        $this -> deleteDirectory(RL_FILES .'qrcode'. RL_DS . 'user_' . $user_id . RL_DS. 'listing_'.$listing_id.'.png');
    }

    /**
     * Uninstall the plugin
     **/
    public function uninstall()
    {
        // delete qrcode folder
        $this -> deleteDirectory(RL_FILES .'qrcode'. RL_DS);
    }

    /**
     * Generate the QR code.
     *
     * @return object this
     */
    public function finish($data)
    {
        $this->_sData = "BEGIN:VCARD\r\n";
        $this->_sData .= "VERSION:2.1\r\n";

        if (!empty($data['Title'])) $this->fullName($data['Title']);
        if (!empty($data['Mail'])) $this->email($data['Mail']);
        if (!empty($data['Phone'])) $this->mobilePhone($data['Phone']);
        if (!empty($data['Name'])) $this->note($data['Name']);
        $this->url($data['Link']);
        $user_dir = RL_FILES . 'qrcode' . RL_DS . 'user_' . $data['Account_ID'];
        if (!is_dir($user_dir)) {
            $this->rlMkdir($user_dir);
        }
        $this->_sData .= 'END:VCARD';
        $Filepath = $user_dir . RL_DS . 'listing_' . $data['ID'] . '.png';
        QRcode::png($this->_sData, $Filepath,  QR_ECLEVEL_L, 3, 2);
    }

    /**
     * The name of the person.
     *
     * @param string $sName
     * @return object this
     */
    public function name($sName)
    {
        $this->_sData .= "N:" . $sName . "\r\n";
    }

    /**
     * The full name of the person.
     *
     * @param string $sFullName
     * @return object this
     */
    public function fullName($sFullName)
    {
        $sFullName = preg_replace('/\s+/', ' ', $sFullName);
        $this->_sData .= "FN:" . $sFullName . "\r\n";
    }

    /**
     * Delivery address.
     *
     * @param string $sAddress
     * @return object this
     */
    public function address($sAddress)
    {
        $this->_sData .= 'ADR:' . $sAddress . "\r\n";
    }

    /**
     * Nickname.
     *
     * @param string $sNickname
     * @return object this
     */
    public function nickName($sNickname)
    {
        $this->_sData .= 'NICKNAME:' . $sNickname . "\r\n";
    }

    /**
     * Email address.
     *
     * @param string $sMail
     * @return object this
     */
    public function email($sMail)
    {
        $this->_sData .= "EMAIL:" . $sMail . "\r\n";
    }

    /**
     * Work Phone.
     *
     * @param string $sVal
     * @return object this
     */
    public function workPhone($sVal)
    {
        $phone = $this->parsePhone($sVal);
        $this->_sData .= "TEL;WORK:+" . implode('', $phone) . "\r\n";
    }

    /**
     * Home Phone.
     *
     * @param string $sVal
     * @return object this
     */
    public function homePhone($sVal)
    {
        $phone = $this->parsePhone($sVal);
        $this->_sData .= "TEL;HOME:+" . implode('', $phone) . "\r\n";
    }

    /**
     * Home Phone.
     *
     * @param string $sVal
     * @return object this
     */
    public function mobilePhone($sVal)
    {
        $this->_sData .= "TEL;CELL:" . $sVal . "\r\n";
    }

    /**
     * URL address.
     *
     * @param string $sUrl
     * @return object this
     */
    public function url($sUrl)
    {
        $sUrl = (substr($sUrl, 0, 4) != 'http') ? 'http://' . $sUrl : $sUrl;
        $this->_sData .= "URL:" . $sUrl . "\r\n";
    }

    /**
     * SMS code.
     *
     * @param string $sPhone
     * @param string $sText
     * @return object this
     */
    public function sms($sPhone, $sText)
    {
        $this->_sData .= 'SMSTO:' . $sPhone . ':' . $sText . "\r\n";
    }

    /**
     * Birthday.
     *
     * @param string $sBirthday Date in the format YYYY-MM-DD or ISO 8601
     * @return object this
     */
    public function birthday($sBirthday)
    {
        $this->_sData .= 'BDAY:' . $sBirthday . "\r\n";
    }

    /**
     * Anniversary.
     *
     * @param string $sBirthDate Date in the format YYYY-MM-DD or ISO 8601
     * @return object this
     */
    public function anniversary($sBirthDate)
    {
        $this->_sData .= 'ANNIVERSARY:' . $sBirthDate . "\r\n";
    }

    /**
     * Gender.
     *
     * @param string $sSex F = Female. M = Male
     * @return object this
     */
    public function gender($sSex)
    {
        $this->_sData .= 'GENDER:' . $sSex . "\r\n";
    }

    /**
     * A list of "tags" that can be used to describe the object represented by this vCard.
     *
     * @param string $sCategory
     * @return object this
     */
    public function categories($sCategories)
    {
        $this->_sData .= 'CATEGORIES:' . $sCategories . "\r\n";
    }

    /**
     * The instant messenger (Instant Messaging and Presence Protocol).
     *
     * @param string $sVal
     * @return object this
     */
    public function impp($sVal)
    {
        $this->_sData .= 'IMPP:' . $sVal . "\r\n";
    }

    /**
     * Photo (avatar).
     *
     * @param string $sImgUrl URL of the image.
     * @return object this
     * @throws InvalidArgumentException If the image format is invalid.
     */
    public function photo($sImgUrl)
    {
        $bIsImgExt = strtolower(substr(strrchr($sImgUrl, '.'), 1)); // Get the file extension.

        if ($bIsImgExt == 'jpeg' || $bIsImgExt == 'jpg' || $bIsImgExt == 'png' || $bIsImgExt == 'gif')
            $sExt = strtoupper($bIsImgExt);
        else
            throw new InvalidArgumentException('Invalid format Image!');

        $this->_sData .= 'PHOTO;VALUE=URL;TYPE=' . $sExt . ':' . $sImgUrl . "\r\n";
    }

    /**
     * The role, occupation, or business category of the vCard object within an organization.
     *
     * @param string $sRole e.g.: Executive
     * @return object this
     */
    public function role($sRole)
    {
        $this->_sData .= 'ROLE:' . $sRole . "\r\n";
    }

    /**
     * The supplemental information or a comment that is associated with the vCard.
     *
     * @param string $sText
     * @return object this
     */
    public function note($sText)
    {
        $this->_sData .= 'NOTE:' . $sText . "\r\n";
    }

    /**
     * Bookmark.
     *
     * @param string $sTitle
     * @param string $sUrl
     * @return object this
     */
    public function bookmark($sTitle, $sUrl)
    {
        $this->_sData .= 'MEBKM:TITLE:' . $sTitle . ';URL:' . $sUrl . "\r\n";
    }

    /**
     * Geo location.
     *
     * @param string $sLat Latitude
     * @param string $sLon Longitude
     * @param integer $iHeight Height
     * @return object this
     */
    public function geo($sLat, $sLon, $iHeight)
    {
        $this->_sData .= 'GEO:' . $sLat . ',' . $sLon . ',' . $iHeight . "\r\n";
    }

    /**
     * The language that the person speaks.
     *
     * @param string $sLang e.g.: en-US
     * @return object this
     */
    public function lang($sLang)
    {
        $this->_sData .= 'LANG:' . $sLang . "\r\n";
    }

    /**
     * Wifi.
     *
     * @param string $sType
     * @param string $sSsid
     * @param string $sPwd
     * @return object this
     */
    public function wifi($sType, $sSsid, $sPwd)
    {
        $this->_sData .= 'WIFI:T:' . $sType . ';S' . $sSsid . ';' . $sPwd . "\r\n";
    }
}