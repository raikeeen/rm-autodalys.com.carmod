<?php

class BoxSize extends ObjectModel
{
    public $id;
    public $size;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lpexpress_box',
        'primary' => 'id_lpexpress_box',
        'fields' => array(
            'size' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
    );

    public function loadBySize($size)
    {
        $query = new DbQuery();
        $query
            ->select('id_lpexpress_box')
            ->from('lpexpress_box')
            ->where('size = "'. pSQL($size) .'"');

        $id_box = Db::getInstance()->getValue($query);
        if ($id_box)
        {
            self::__construct($id_box);
        }
    }

    public function isAssociatedWithTerminal($id_terminal)
    {
        $query = new DbQuery();
        $query
            ->select('COUNT(*)')
            ->from('lpexpress_terminal_box')
            ->where('id_lpexpress_terminal = '.(int) $id_terminal)
            ->where('id_lpexpress_box = '.(int) $this->id);

        $result = Db::getInstance()->getValue($query);
        return $result > 0;
    }

    public static function getBoxSize($id_box)
    {
        $query = new DbQuery();
        $query
            ->select('size')
            ->from('lpexpress_box')
            ->where('id_lpexpress_box = '.(int) $id_box);

        return Db::getInstance()->getValue($query);
    }

    public static function getAllBoxSizes()
    {
        $query = new DbQuery();
        $query
            ->select('lb.*')
            ->from('lpexpress_box', 'lb')
            ->innerJoin('lpexpress_terminal_box', 'ltb', 'lb.id_lpexpress_box = ltb.id_lpexpress_box')
            ->innerJoin('lpexpress_terminal', 'lt', 'lt.id_lpexpress_terminal = ltb.id_lpexpress_terminal')
            ->where('lt.active = 1')
            ->groupBy('lb.id_lpexpress_box');

        return Db::getInstance()->executeS($query);
    }
}