<?php
/**
 * Bootstrap PHPMailer 6.9.x (compatível PHP 7.3–8.4).
 * Expõe a classe global PHPMailer com aliases PascalCase dos callers legados.
 * Nota: nomes de método em PHP são case-insensitive — aliases chamam parent::*.
 */
if (class_exists('PHPMailer', false)) {
    return;
}

require_once __DIR__ . '/src/Exception.php';
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';

class PHPMailer extends \PHPMailer\PHPMailer\PHPMailer
{
    public function IsSMTP()
    {
        return parent::isSMTP();
    }

    public function IsMail()
    {
        return parent::isMail();
    }

    public function IsSendmail()
    {
        return parent::isSendmail();
    }

    public function IsQmail()
    {
        return parent::isQmail();
    }

    public function IsHTML($isHtml = true)
    {
        return parent::isHTML($isHtml);
    }

    public function AddAddress($address, $name = '')
    {
        return parent::addAddress($address, $name);
    }

    public function AddCC($address, $name = '')
    {
        return parent::addCC($address, $name);
    }

    public function AddBCC($address, $name = '')
    {
        return parent::addBCC($address, $name);
    }

    public function AddReplyTo($address, $name = '')
    {
        return parent::addReplyTo($address, $name);
    }

    public function AddAttachment($path, $name = '', $encoding = 'base64', $type = '', $disposition = 'attachment')
    {
        return parent::addAttachment($path, $name, $encoding, $type, $disposition);
    }

    public function SetFrom($address, $name = '', $auto = true)
    {
        return parent::setFrom($address, $name, $auto);
    }

    public function Send()
    {
        return parent::send();
    }

    public function ClearAddresses()
    {
        parent::clearAddresses();
    }

    public function ClearAllRecipients()
    {
        parent::clearAllRecipients();
    }

    public function ClearAttachments()
    {
        parent::clearAttachments();
    }

    public function ClearReplyTos()
    {
        parent::clearReplyTos();
    }

    public function ClearCustomHeaders()
    {
        parent::clearCustomHeaders();
    }

    /**
     * Aceita typo legado Charset → CharSet (evita dynamic property em PHP 8.2+).
     */
    public function __set($name, $value)
    {
        if (strcasecmp($name, 'Charset') === 0) {
            $this->CharSet = $value;
        }
    }
}
