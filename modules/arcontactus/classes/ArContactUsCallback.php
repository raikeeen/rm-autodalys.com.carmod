<?php
/**
* 2012-2017 Azelab
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*  @author    Azelab <support@azelab.com>
*  @copyright 2017 Azelab
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Azelab
*/

class ArContactUsCallback extends ObjectModel
{
    const TABLE_NAME = 'arcontactus_callback';
    
    public $id;
    public $id_user;
    public $phone;
    public $created_at;
    public $updated_at;
    public $status;
    public $comment;

    const STATUS_NEW = 0;
    const STATUS_DONE = 1;
    const STATUS_IGNORE = 2;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_callback',
        'multilang' => false,
        'fields' => array(
            'id_user' =>            array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'phone' =>              array('type' => self::TYPE_STRING),
            'created_at' =>         array('type' => self::TYPE_STRING),
            'updated_at' =>         array('type' => self::TYPE_STRING),
            'status' =>             array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'comment' =>            array('type' => self::TYPE_STRING),
        ),
    );
    
    public static function getAll()
    {
        $sql = new DbQuery();
        $sql->from(self::TABLE_NAME, 't');
        $sql->orderBy('created_at DESC');
        $res = Db::getInstance()->executeS($sql);
        
        return $res;
    }
    
    public static function addCallback($id_user, $phone)
    {
        $model = new self();
        $model->id_user = (int)$id_user;
        $model->phone = pSQL($phone);
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
        return $model;
    }
}
