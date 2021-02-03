<?php

class LPOrder extends ObjectModel
{
    const TYPE_TERMINAL = 'terminal';
    const TYPE_ADDRESS = 'address';
    const TYPE_POST = 'post';

    public $id_cart;
    public $id_order;
    public $id_lpexpress_terminal;
    public $id_lpexpress_box;
    public $type;
    public $weight;
    public $packets;
    public $cod;
    public $cod_amount;
    public $comment;
    public $label_number;
    public $orderid;
    public $identcode;
    public $orderpdfid;
    public $manifestid;
    public $post_address;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lpexpress_terminal_order',
        'primary' => 'id_lpexpress_terminal_order',
        'fields' => array(
            'id_cart' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_order' => array('type' => self::TYPE_INT),
            'id_lpexpress_terminal' => array('type' => self::TYPE_INT),
            'id_lpexpress_box' => array('type' => self::TYPE_INT),
            'type' => array('type' => self::TYPE_STRING),
            'weight' => array('type' => self::TYPE_FLOAT, 'validate' => 'isUnsignedFloat'),
            'packets' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'cod' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'cod_amount' => array('type' => self::TYPE_FLOAT),
            'comment' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'label_number' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'orderid' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'identcode' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'orderpdfid' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'manifestid' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'post_address' => array('type' => self::TYPE_STRING, 'validate' => 'isString')
        ),
    );

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->label_number = 0;
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function loadByCartID($id_cart)
    {
        $query = new DbQuery();
        $query
            ->select('id_lpexpress_terminal_order')
            ->from('lpexpress_terminal_order')
            ->where('id_cart = '.(int) $id_cart);

        $id_terminal_order = Db::getInstance()->getValue($query);
        return $this->__construct($id_terminal_order);
    }

    public function loadByOrderID($id_order)
    {
        $query = new DbQuery();
        $query
            ->select('id_lpexpress_terminal_order')
            ->from('lpexpress_terminal_order')
            ->where('id_order = '.(int) $id_order);

        $id_terminal_order = Db::getInstance()->getValue($query);
        return $this->__construct($id_terminal_order);
    }

    public function loadByIdentCode($identcode)
    {
        $query = new DbQuery();
        $query
            ->select('id_lpexpress_terminal_order')
            ->from('lpexpress_terminal_order')
            ->where('identcode = "'.pSQL($identcode).'"');

        $id_terminal_order = Db::getInstance()->getValue($query);
        return $this->__construct($id_terminal_order);
    }

    public function save($null_values = false, $auto_date = true)
    {
        $this->cleanup();
        return parent::save($null_values, $auto_date);
    }

    /**
     * After order confirmation cleanup and remove unnecessary variables
     */
    public function cleanup()
    {

    }

    public function isAdded()
    {
        return !empty($this->orderid);
    }

    public function isConfirmed()
    {
        return !empty($this->identcode);
    }

    public function isManifestCreated()
    {
        return !empty($this->manifestid);
    }

    public static function getCourierOrders()
    {
        $query = new DbQuery();
        $query
            ->select('id_order')
            ->from('lpexpress_terminal_order')
            ->where('id_order != 0')
            ->where('identcode != ""')
            ->where('manifestid = ""');
        return Db::getInstance()->executeS($query);
    }
}