<?php

/*
  Plugin Name: DeMomentSomTres Tools
  Plugin URI: http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-tools/
  Description: DeMomentSomTres Tools is a function library and utilities used by all DeMomentSomTres plugins
  Version: 0.1
  Author: Marc Queralt
  Author URI: http://demomentsomtres.com
 */

define('DMST_TOOLS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DMST_TOOLS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DMST_TOOLS_LANG_DIR', dirname(plugin_basename(__FILE__)) . '/languages');
define('DMST_TOOLS_TEXT_DOMAIN', 'DeMomentSomTres-Tools');

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}

class DeMomentSomTresTools {

    /**
     * @since 0.1
     */
    function DeMomentSomTresTools() {
        add_action('plugins_loaded', array(&$this, 'plugin_init'));
    }

    /**
     * @since 0.1
     */
    function plugin_init() {
        load_plugin_textdomain(DMST_TOOLS_TEXT_DOMAIN, false, DMST_TOOLS_LANG_DIR);
    }

    /** ADMIN HELPER FUNCTIONS * */

    /**
     * Recursive applies esc_attr to a variable content array
     * @param array $x
     * @return array
     * @since 0.1
     */
    public static function adminHelper_esc_attr($x) {
        if (is_array($x)):
            foreach($x as $k=>$v):
                $x[$k]=self::adminHelper_esc_attr($v);
            endforeach;
            return $x;
        else:
            return esc_attr($x);
        endif;
    }

    /**
     * Writes the options field with value in the admin page
     * @param string $prefix the option prefix name (content before the [ symbol). Since 1.1 considers the possibility of provinding null prefix in order to use name as $field only allowing complex fields.
     * @param string $field the option name
     * @param string $value the option value 
     * @param string $type specifies the type of input. If not set, it will be treated as a text box. Allowed values: 'page-dropdown' to show a drop down of pages, 'radio' to show a radio button, 'checkbox' to show a checkbox, 'list' as select, 'textarea', 'editor'
     * @param mixed $checked
     * @param string $html_before html to print before the begining of field
     * @param string $html_after html to print after the end of the field
     * @param string $label if set is showed as the radio button element label
     * @param array $list an array containing pairs of 'id','name'
     * @param integer $cols textarea cols number
     * @param integer $rows textarea rows number
     * @param boolean $wpautop wp_editor wpautop parameter
     * @since 0.1
     */
    public static function adminHelper_input($prefix, $field, $value, $type = null, $checked = false, $label = null, $html_before = '', $html_after = '', $list = array(), $listNone = '-------', $cols = 100, $rows = 25, $wpautop = true) {
        if ($prefix):
            $name = $prefix . '[' . $field . ']';
            $id = $field;
        else:
            $name = $field;
            $id = $field;
        endif;
        echo $html_before;
        switch ($type):
            case 'page-dropdown':
                wp_dropdown_pages(array('name' => $name, 'show_option_none' => __('&mdash; Select &mdash;'), 'option_none_value' => '0', 'selected' => $value));
                break;
            case 'radio':
                echo "<input id='$id' name='$name' type='radio' value='$value'" . checked($value, $checked, false) . " />";
                if (isset($label)):
                    echo " <label>$label</label>";
                endif;
                break;
            case 'checkbox':
                echo "<input id='$id' name='$name' type='checkbox'" . checked($value, 'on', false) . " />";
                break;
            case 'list':
                echo "<select id='$id' name='$name'>";
                echo "<option value=''>" . $listNone . "</option>";
                foreach ($list as $l):
                    $lid = $l['id'];
                    $n = $l['name'];
                    echo "<option value='$lid' " . selected($value, $lid) . ">$n</option>";
                endforeach;
                echo "</select>";
                break;
            case 'textarea':
                echo "<textarea id='$id' name='$name' cols=$cols rows=$rows>$value</textarea>";
                break;
            case 'editor':
                wp_editor($value, $id, array('wpautop' => $wpautop, 'textarea_name' => $name));
                break;
            default:
                echo "<input id='$id' name='$name' type='text' value='$value'/>";
        endswitch;
        echo $html_after;
    }

    /**
     * Gets a the prefix['name'] option and, if not set, returns the default value provided
     * @param string $prefix the option prefix name (content before the [ symbol)
     * @param string $name the option name
     * @param mixed $default the default value
     * @return mixed the option new value
     * @since 0.1
     */
    public static function adminHelper_get_option($prefix, $name, $default = null) {
        $options = get_option($prefix);
        if (!isset($options[$name])):
            return $default;
        else:
            return $options[$name];
        endif;
    }
}

?>
