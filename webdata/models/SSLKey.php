<?php

class SSLKey extends Pix_Table
{
    public function init()
    {
        $this->_primary = 'domain';
        $this->_name = 'ssl_keys';

        $this->_columns['domain'] = array('type' => 'char', 'size' => 64);
        $this->_columns['config'] = array('type' => 'text');
    }
}
