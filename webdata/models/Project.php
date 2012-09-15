<?php

class ProjectRow extends Pix_Table_Row
{
    public function getFirstDomain()
    {
        // TODO: add custom domain
        return $this->name . USER_DOMAIN;
    }

    public function getLoggerCategory()
    {
        return $this->name . '_' . hash_hmac('sha256', $this->name, getenv('LOG_SECRET'));
    }
}

class Project extends Pix_Table
{
    public function init()
    {
        $this->_name = 'project';
        $this->_primary = 'id';
        $this->_rowClass = 'ProjectRow';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 64);
        $this->_columns['commit'] = array('type' => 'char', 'size' => 32);
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['created_by'] = array('type' => 'int');

        $this->_indexes['name'] = array('type' => 'unique', 'columns' => array('name'));

        $this->_relations['members'] = array('rel' => 'has_many', 'type' => 'ProjectMember', 'foreign_key' => 'project_id');
        $this->_relations['custom_domains'] = array('rel' => 'has_many', 'type' => 'CustomDomain', 'foreign_key' => 'project_id');
        $this->_relations['variables'] = array('rel' => 'has_many', 'type' => 'ProjectVariable', 'foreign_key' => 'project_id');
        $this->_relations['webnodes'] = array('rel' => 'has_many', 'type' => 'WebNode', 'foreign_key' => 'project_id');

    }

    public static function getRandomName()
    {
        $areas = array('taipei', 'taoyuan', 'hsinchu', 'yilan', 'hualien', 'miaoli', 'taichung', 'changhua', 'nantou', 'chiayi', 'yunlin', 'tainan', 'penghu', 'kaohiung', 'pingtung', 'kinmen', 'matsu', 'taitung');
        $first_names = array('An', 'Chang', 'Chao', 'Chen', 'Cheng', 'Chi', 'Chiang', 'Chien', 'Chin', 'Chou', 'Chu', 'Fan', 'Fang', 'Fei', 'Feng', 'Fu', 'Han', 'Hao', 'Ho', 'Hsi', 'Hsiao', 'Hsieh', 'Hsu', 'Hsueh', 'Hua', 'Huang', 'Jen', 'Kang', 'Ko', 'Ku', 'Kung', 'Lang', 'Lei', 'Li', 'Lien', 'Liu', 'Lo', 'Lu', 'Ma', 'Meng', 'Miao', 'Mu', 'Ni', 'Pai', 'Pan', 'Pao', 'Peng', 'Pi', 'Pien', 'Ping', 'Pu', 'Shen', 'Shih', 'Shui', 'Su', 'Sun', 'Tang', 'Tao', 'Teng', 'Tou', 'Tsao', 'Tsen', 'Tsou', 'Wang', 'Wei', 'Wu', 'Yang', 'Yen', 'Yin', 'Yu', 'Yuan', 'Yueh', 'Yun');

        for ($i = 0; $i < 10; $i ++) {
            $random = strtolower($areas[rand(0, count($areas) - 1)] . '-' . $first_names[rand(0, count($first_names) - 1)] . '-' . rand(100000, 1000000));

            if (!Project::find_by_name($random)) {
                break;
            }
        }

        if ($i > 5) {
            trigger_error("random {$i} times... too much times", E_USER_WARNING);
        }
        return $random;
    }
}
