<?php

/*
  Plugin Name: DeMomentSomTres Tools
  Plugin URI: http://demomentsomtres.com/english/wordpress-plugins/demomentsomtres-tools/
  Description: DeMomentSomTres Tools is a function library and utilities used by all DeMomentSomTres plugins
  Version: 2.0
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
$demomentsomtres_tools = new DeMomentSomTresTools;

class DeMomentSomTresTools {

    const MAILCHIMP_SUCCESS = 'success';
    const MAILCHIMP_ERROR = 'error';

    /**
     * @since 1.0
     */
    function DeMomentSomTresTools() {
        add_action('plugins_loaded', array(&$this, 'plugin_init'));
    }

    /**
     * @since 1.0
     */
    function plugin_init() {
        load_plugin_textdomain(DMST_TOOLS_TEXT_DOMAIN, false, DMST_TOOLS_LANG_DIR);
    }

    /** ADMIN HELPER FUNCTIONS * */

    /**
     * Recursive applies esc_attr to a variable content array
     * @param array $x
     * @return array
     * @since 1.0
     */
    public static function adminHelper_esc_attr($x) {
        if (is_array($x)):
            foreach ($x as $k => $v):
                $x[$k] = self::adminHelper_esc_attr($v);
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
     * @since 1.0
     * @deprecated 1.1
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
     * Writes the options field with value in the admin page using an array of parameters
     * @param string $prefix the option prefix name (content before the [ symbol). Since 1.1 considers the possibility of provinding null prefix in order to use name as $field only allowing complex fields.
     * @param string $field the option name
     * @param string $value the option value 
     * @param array $params with the following elements
     *              'type': specifies the type of input. If not set, it will be treated as a text box. Allowed values: 'page-dropdown' to show a drop down of pages, 'radio' to show a radio button, 'checkbox' to show a checkbox, 'list' as select, 'textarea', 'editor'
     *              'checked'
     *              'html_before': html to print before the field
     *              'html_after': html to print after the field
     *              'label': if set is showed as the radio button element label
     *              'list': an array containg paris of 'id','name'
     *              'list
     *              'cols': textarea cols number
     *              'rows': textarea rows number
     *              'wpautop': (boolean) wp_editor wpautop parameter
     *              'class': css classes of the input field 
     *              'echo': (boolean) if set to false returns the value of the field instead of echoing it. Default: true.
     *                      echo is not compatible with editor type.
     * @since 1.1
     */
    public static function adminHelper_inputArray($prefix, $field, $value, $params = array()) {
        if ($prefix):
            $name = $prefix . '[' . $field . ']';
            $id = $field;
        else:
            $name = $field;
            $id = $field;
        endif;
        $params = wp_parse_args((array) $params, array(
            'type' => null,
            'checked' => false,
            'label' => null,
            'html_before' => '',
            'html_after' => '',
            'list' => array(),
            'listNone' => '-------',
            'cols' => 100,
            'rows' => 25,
            'wpautop' => true,
            'class' => '',
            'echo' => true
        ));
        extract($params, EXTR_SKIP);
        if ('' != $class)
            $classes = " class='$class' ";
        else
            $classes = "";
        $result = $html_before;
        switch ($type):
            case 'page-dropdown':
                $result.=wp_dropdown_pages(array('name' => $name, 'show_option_none' => __('&mdash; Select &mdash;'), 'option_none_value' => '0', 'selected' => $value, 'echo' => 0));
                break;
            case 'radio':
                $result.= "<input id='$id' name='$name' type='radio' value='$value'" . checked($value, $checked, false) . "$classes/>";
                if (isset($label)):
                    $result.= " <label>$label</label>";
                endif;
                break;
            case 'checkbox':
                $result.= "<input id='$id' name='$name' type='checkbox'" . checked($value, 'on', false) . "$classes/>";
                break;
            case 'list':
                $result.= "<select id='$id' name='$name'$classes>";
                $result.= "<option value=''>" . $listNone . "</option>";
                foreach ($list as $l):
                    $lid = $l['id'];
                    $n = $l['name'];
                    $result.= "<option value='$lid' " . selected($value, $lid, false) . ">$n</option>";
                endforeach;
                $result.= "</select>";
                break;
            case 'textarea':
                $result.= "<textarea id='$id' name='$name' cols=$cols rows=$rows$classes>$value</textarea>";
                break;
            case 'editor':
                wp_editor($value, $id, array('wpautop' => $wpautop, 'textarea_name' => $name));
                break;
            default:
                $result.= "<input id='$id' name='$name' type='text' value='$value'$classes/>";
        endswitch;
        $result.= $html_after;
        if (!$echo):
            return $result;
        else:
            echo $result;
        endif;
    }

    /**
     * Get a the prefix['name'] option and, if not set, returns the default value provided
     * Use get_option instead
     * @param string $prefix the option prefix name (content before the [ symbol)
     * @param string $name the option name
     * @param mixed $default the default value
     * @return mixed the option new value
     * @since 1.0
     * @deprecated 1.1 
     */
    public static function adminHelper_get_option($prefix, $name, $default = null) {
        return self::get_option($prefix, $name, $default);
    }

    /**
     * Gets a the prefix['name'] option and, if not set, returns the default value provided
     * @param string $prefix the option prefix name (content before the [ symbol)
     * @param string $name the option name
     * @param mixed $default the default value
     * @return mixed the option new value
     * @since 1.0
     */
    public static function get_option($prefix, $name, $default = null) {
        $prefixes = explode('[', $prefix);
        $firstPrefix = array_shift($prefixes);
        $options = get_option(trim($firstPrefix, ']'));
        while (!empty($prefixes)):
            $thePrefix = trim(array_shift($prefixes), ']');
            if (isset($options[$thePrefix])):
                $options = $options[$thePrefix];
            else:
                break;
            endif;
        endwhile;
        if (!isset($options[$name])):
            return $default;
        else:
            return $options[$name];
        endif;
    }

    /**
     * Inits a MailChimp API session
     * @param string $api
     * @return \DeMomentSomTresMailChimp|null
     * @since 1.1
     */
    public static function MailChimpSession($api) {
        if ($api == ''):
            return null;
        endif;
        return new DeMomentSomTresMailChimp($api);
    }

    /**
     * Get MailChimp Lists
     * @param mixed $mcSession MailChimp Session
     * @param boolean $groups if true groups will be downloaded if they exist
     * @return array associative array with all List containing id, name, groups
     * @since 1.1
     */
    public static function MailChimpGetLists($mcSession, $groups = false) {
        $result = array();
        if ($mcSession):
            $temp = $mcSession->call('lists/list');
            if (isset($temp['data'])):
                foreach ($temp['data'] as $t):
                    if ($groups && ($t['stats']['group_count'] != 0)):
                        $gs = array();
                        $temp2 = $mcSession->call('lists/interest-groupings', array(
                            'id' => $t['id'],
                            'counts' => true
                        ));
                        foreach ($temp2 as $t2):
                            $groupings = array();
                            $temp3 = $t2['groups'];
                            foreach ($temp3 as $t3):
                                $gs[] = array(
                                    'id' => $t3['id'],
                                    'name' => $t3['name'],
                                    'subscribers' => $t3['subscribers'],
                                );
                            endforeach;
                            $groupings[] = array(
                                'id' => $t2['id'],
                                'name' => $t2['name'],
                                'groups' => $gs
                            );
                        endforeach;
                        $result[] = array(
                            'id' => $t['id'],
                            'name' => $t['name'],
                            'groupings' => $groupings,
                            'subscribers' => $t['stats']['member_count'],
                        );
                    else:
                        $result[] = array(
                            'id' => $t['id'],
                            'name' => $t['name'],
                            'subscribers' => $t['stats']['member_count'],
                        );
                    endif;
                endforeach;
            endif;
        endif;
        return $result;
    }

    /**
     * Get MailChimp Templates
     * @param mixed $mcSession MailChimp Session
     * @return array associative array containing id, name for each template
     * @since 1.1
     */
    public static function MailChimpGetTemplates($mcSession) {
        $result = array();
        if ($mcSession):
            $temp = $mcSession->call('templates/list');
            if (isset($temp['user'])):
                foreach ($temp['user'] as $t):
                    $result[] = array(
                        'id' => $t['id'],
                        'name' => $t['name']
                    );
                endforeach;
            endif;
        endif;
        return $result;
    }

    /**
     * @param mixed $mcSession a mailchimp api session
     * @param string $listid a list id
     * @param string $groupingid a grouping id in the list
     * @param string $groupid a group id in the grouping
     * @return string the group name|null if not found
     * @since 2.0
     */
    public static function MailChimpGetGroupName($mcSession, $listid, $groupingid, $groupid) {
        if ($mcSession):
            $interestGroups = $mcSession->call('lists/interest-groupings', array(
                'id' => $listid,
                'counts' => true
            ));
            foreach ($interestGroups as $grouping):
                foreach ($grouping['groups'] as $group):
                    if ($group['id'] == $groupid):
                        return $group['name'];
                    endif;
                endforeach;
            endforeach;
        endif;
        return $result;
    }

    /**
     * 
     * @param mixed $mcSession a mailchimp api session
     * @param string $email an email
     * @param string $listid a list id
     * @param boolean $withGroups
     * @return array associative array containing subscribedToList and groupings
     * @since 2.0
     */
    public static function MailChimpGetEmailListSubscription($mcSession, $email, $listid, $withGroups) {
        if ($mcSession):
            $subscription = $mcSession->call('lists/member-info', array(
                'id' => $listid,
                'emails' => array(
                    array(
                        'email' => $email,
                    )
                ),
            ));
            if (isset($subscription['errors'][0])):
                $result['subscribedToList'] = FALSE;
                $result['errors'] = $subscription['errors'];
            else:
                $result['subscribedToList'] = ($subscription['data'][0]['status']!='unsubscribed');
                $result['groupings'] = $subscription['data'][0]['merges']['GROUPINGS'];
            endif;
        endif;
        return $result;
    }

    /**
     * 
     * @param mixed $mcSession
     * @param string $listid
     * @param string $email
     * @param boolean $doubleOptin
     * @param boolean $sendWelcome
     * @return type
     * @since 2.0
     */
    public static function MailChimpSubscribeToList($mcSession, $listid, $email, $groupings = null, $doubleOptin = true, $sendWelcome = false) {
        if ($mcSession):
            $subscribe = $mcSession->call('lists/subscribe', array(
                'id' => $listid,
                'email' => array(
                    'email' => $email,
                ),
                'merge_vars' => array(
                    'groupings' => $groupings,
                ),
                'double_optin' => $doubleOptin,
                'send_welcom' => $sendWelcome,
                'update_existing' => FALSE,
                'replace_interests' => FALSE,
            ));
            if (isset($subscribe['status'])):
                $result = array(
                    'status' => DeMomentSomTresTools::MAILCHIMP_ERROR,
                    'message' => sprintf(__('An error while subscribing %s happened', DMST_TOOLS_TEXT_DOMAIN), $email),
                    'error' => $subscribe,
                );
            else:
                $result = array(
                    'status' => DeMomentSomTresTools::MAILCHIMP_SUCCESS,
                    'message' => sprintf(__('%s successfully subscribed to the list', DMST_TOOLS_TEXT_DOMAIN), $email),
                );
            endif;
        endif;
        return $result;
    }

    /**
     * @since 2.0
     */
    public static function MailChimpUpdateMemberListGroups($mcSession, $listid, $email, $newGroupings) {
        if ($mcSession):
            $update = $mcSession->call('lists/update-member', array(
                'id' => $listid,
                'email' => array(
                    'email' => $email,
                ),
                'merge_vars' => array(
                    'groupings' => $newGroupings,
                ),
            ));
        endif;
        $result = $update;
        return $result;
    }

    /**
     * @since 2.0
     */
    public static function MailChimpUnsubscribeFromList($mcSession, $listid, $email, $deleteMember = FALSE, $sendGoodbye = TRUE, $sendNotify = TRUE) {
        if ($mcSession):
            $status = DeMomentSomTresTools::MAILCHIMP_SUCCESS;
            $unsubscribe = $mcSession->call('lists/unsubscribe', array(
                'id' => $listid,
                'email' => array(
                    'email' => $email,
                ),
                'delete_member' => $deleteMember,
                'send_goodbye' => $sendGoodbye,
                'send_notify' => $sendNotify,
            ));
            if (isset($unsubscribe['status'])):
                $result = $unsubscribe;
                $status = DeMomentSomTresTools::MAILCHIMP_ERROR;
            endif;
        endif;
        return array(
            'status' => $status,
            'result' => $result
        );
    }

}
