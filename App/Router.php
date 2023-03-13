<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-2.0.html
 * @link        https://www.wployalty.net
 * */
namespace Wlac\App;
use Wlac\App\Controllers\Site\Main;

defined('ABSPATH') or die;
class Router {
    private static $site,$admin;
    function init(){
        self::$site = empty(self::$site) ? new Main() : self::$site;
        //self::$admin = empty(self::$admin) ? new Main() : self::$admin;
    }
}