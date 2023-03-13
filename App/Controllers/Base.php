<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */

namespace Wlac\App\Controllers;

use Wlr\App\Helpers\Input;
use Wlr\App\Helpers\Template;
use Wlr\App\Helpers\Woocommerce;

defined('ABSPATH') or die;

class Base
{
    public static $input, $woocommerce, $template, $rule;

    function isBasicSecurityValid($nonce_name = '')
    {
        $wlr_nonce = (string)self::$input->post_get('wlr_nonce', '');
        if (!Woocommerce::hasAdminPrivilege() || !Woocommerce::verify_nonce($wlr_nonce, $nonce_name)) return false;
        return true;
    }
}