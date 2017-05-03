<?php

class SignupConfirmRow extends Pix_Table_Row
{
    public function sendMail()
    {
        $p = new Pix_Partial(__DIR__ . '/../views/');

        $expired_at = time() + 3600;
        $mail = $this->email;
        $sig = crc32($mail . $expired_at . $this->code);

        $body = $p->partial('mail/signupconfirm.phtml', array(
            'email' => $this->email,
            'code' => $this->code,
            'domain' => getenv('MAINPAGE_DOMAIN'),
            'signup_url' => 'https://' . getenv('MAINPAGE_DOMAIN') . '/index/signupconfirm?mail=' . urlencode($this->email) . '&expired_at=' . $expired_at . '&sig=' . $sig,
        ));

        NotifyLib::alert(
            sprintf("[%s] 註冊信", getenv('MAINPAGE_DOMAIN')),
            $body,
            $this->email
        );
    }
}

class SignupConfirm extends Pix_Table
{
    public function init()
    {
        $this->_name = 'signup_confirm';
        $this->_primary = 'id';
        $this->_rowClass = 'SignupConfirmRow';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['email'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['sent_at'] = array('type' => 'int');
        $this->_columns['code'] = array('type' => 'int');

        $this->addIndex('email', array('email'));
    }

    public static function sendSignupConfirm($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("{$email} 不是合法的信箱");
        }

        if (User::find_by_name($email)) {
            throw new Exception("{$email} 已被註冊");
        }

        if ($sc = SignupConfirm::search(array('email' => $email))->order('created_at DESC')->first()) {
            if ($sc->sent_at > time() - 3600) {
                throw new Exception("一小時內才寄過認證信，請稍後再試");
            }
        } else {
            $sc = SignupConfirm::insert(array(
                'created_at' => time(),
                'email' => $email,
                'code' => rand(10000000, 99999999),
            ));
        }
        $sc->sendMail();
    }
}
