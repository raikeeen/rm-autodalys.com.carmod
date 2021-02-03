<?php

class Terminal extends ObjectModel
{
    public $id;
    public $machineid;
    public $active;
    public $name;
    public $address;
    public $zip;
    public $city;
    public $comment;
    public $inside;
    public $boxcount;
    public $collectinghours;
    public $workinghours;
    public $latitude;
    public $longitude;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'lpexpress_terminal',
        'primary' => 'id_lpexpress_terminal',
        'fields' => array(
            'machineid' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'address' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'zip' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 10),
            'city' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'comment' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 256),
            'inside' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'boxcount' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'collectinghours' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'workinghours' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'latitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'longitude' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        ),
    );

    public function loadByMachineID($machineid)
    {
        $query = new DbQuery();
        $query
            ->select('id_lpexpress_terminal')
            ->from('lpexpress_terminal')
            ->where('machineid = "'. pSQL($machineid) .'"');

        $id_terminal = Db::getInstance()->getValue($query);
        if ($id_terminal)
        {
            self::__construct($id_terminal);
        }
    }

    public function removeBoxes()
    {
        $query = new DbQuery();
        $query
            ->type('DELETE')
            ->from('lpexpress_terminal_box')
            ->where('id_lpexpress_terminal = "'.(int) $this->id.'"');

        return Db::getInstance()->query($query);
    }

    public function addBox($id_box)
    {
        $query = 'INSERT INTO `'._DB_PREFIX_.'lpexpress_terminal_box` VALUES ('.(int) $this->id.', '.(int) $id_box.')';
        return Db::getInstance()->query($query);
    }

    public static function toggleActive($id_terminal)
    {
        $query = 'UPDATE `'._DB_PREFIX_.'lpexpress_terminal` SET active = !active WHERE id_lpexpress_terminal = '.(int) $id_terminal;
        return Db::getInstance()->query($query);
    }

    public static function getTerminals($active = true, $order_by = 'city')
    {
        $query = new DbQuery();
        $query
            ->select('*')
            ->from('lpexpress_terminal');

        if ($active)
        {
            $query->where('active = 1');
        }

        if ($order_by)
        {
            $query->orderBy(pSQL($order_by));
        }

        $result = Db::getInstance()->executeS($query);

        $terminals = [];
        if (!empty($result))
        {
            foreach ($result as $terminal)
            {
                $terminals[$terminal['city']][$terminal['id_lpexpress_terminal']] = $terminal;
            }
        }
        return $terminals;
    }

    public static function getTerminalsWithAvailableBoxSizes($active = true, $order_by = 't.city')
    {
        $query = new DbQuery();
        $query
            ->select('t.*')
            ->select('b.*')
            ->from('lpexpress_terminal', 't')
            ->innerJoin('lpexpress_terminal_box', 'bt', 't.id_lpexpress_terminal = bt.id_lpexpress_terminal')
            ->innerJoin('lpexpress_box', 'b', 'bt.id_lpexpress_box = b.id_lpexpress_box')
            ->orderBy('t.id_lpexpress_terminal');

        if ($active)
        {
            $query->where('t.active = 1');
        }

        if ($order_by)
        {
            $query->orderBy(pSQL($order_by));
        }
        $query->orderBy('bt.id_lpexpress_box');

        $result = Db::getInstance()->executeS($query);

        $terminals = [];
        if (!empty($result))
        {
            foreach ($result as $terminal)
            {
                if (!isset($terminals[$terminal['id_lpexpress_terminal']]))
                {
                    $terminals[$terminal['id_lpexpress_terminal']] = $terminal;
                }
                $terminals[$terminal['id_lpexpress_terminal']]['boxes'][] = [
                    'id_lpexpress_box' => $terminal['id_lpexpress_box'],
                    'size' => $terminal['size']
                ];
            }
        }
        return $terminals;
    }

    public static function getTerminalMachineID($id_terminal)
    {
        $query = new DbQuery();
        $query
            ->select('machineid')
            ->from('lpexpress_terminal')
            ->where('id_lpexpress_terminal = '.(int) $id_terminal);

        return Db::getInstance()->getValue($query);
    }

    /**
     * Disable all terminal where older than given date
     * @param $date
     * @return bool|mysqli_result|PDOStatement|resource
     */
    public static function disableOutdatedTerminals($date)
    {
        $query = 'UPDATE `'._DB_PREFIX_.'lpexpress_terminal` SET active = 0 WHERE date_upd < "'.pSQL($date).'"';
        return Db::getInstance()->query($query);
    }
}