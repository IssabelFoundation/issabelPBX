<?php
require_once("superfecta_base.php");
$superfecta = new superfecta_base;
//require_once('JSON/JSON.php');

$root_dir = dirname(dirname(__FILE__));

$out = array("success" => false);
global $db;

switch($_REQUEST['type']) {
case "display_debug":
    echo "ok";
    die();
    break;
case "update_sources":
    $sources_list = json_decode($_REQUEST['data']);
    $sources_commaed = implode(",", $sources_list);
    $sql = "REPLACE INTO superfectaconfig (value, source, field) VALUES('".$db->escapeSimple($sources_commaed)."', '".$db->escapeSimple($_REQUEST['scheme'])."', 'sources')";
    sql($sql);   
    $out = array("success" => true);
    break;
case "update_schemes":
    $scheme_list = json_decode($_REQUEST['data']);
    $order = 1;
    foreach($scheme_list as $data) {
        //$data = preg_replace('/^base_/i', '', $data);
        $sql = "REPLACE INTO superfectaconfig (value, source, field) VALUES('".$db->escapeSimple($order)."', '".$db->escapeSimple($data)."', 'order')";
        sql($sql);
        $order++;
    }
    $sql = "SELECT source FROM superfectaconfig WHERE field='status' and value='disabled'";
    $results = $db->getAll($sql, DB_FETCHMODE_ASSOC);
    if(!$db->IsError($results)) {
        foreach($results as $row) {
            $currentsource = $row['source'];
            $sql2 = "SELECT source FROM superfectaconfig WHERE field='order' AND value>0 AND source='".$db->escapeSimple($currentsource)."'";
            $results2 = $db->getAll($sql, DB_FETCHMODE_ASSOC);
            if(!$db->IsError($results2)) {
                foreach($results2 as $row2) {
                    $sql = "UPDATE superfectaconfig SET value=value*-1 WHERE source='".$db->escapeSimple($currentsource)."' AND field='order'";
                    $db->query($sql);
                }
            }
        }
    }

    $out = array("success" => true);
    break;
case "update_scheme":
    $errors = superfecta_update_scheme($_POST);
    if($errors=='') {
        print '<p><strong>CID Scheme Updated</strong></p>';
    } else {
        print '<p><strong>'.$errors.'</strong></p>';
    }
    break;
case "delete_scheme":
    superfecta_delete_scheme($_REQUEST);
    $out = array("success" => true);
    break;
case "power_scheme":
    //$data = preg_replace('/^scheme_/i', '', $_REQUEST['scheme']);
    $data = $_REQUEST['scheme'];
    $status = $_REQUEST['schemestatus'];
    $sql = "UPDATE superfectaconfig SET value = (value * -1) WHERE field = 'order' AND source = '".$db->escapeSimple($data)."'";
    sql($sql);
    $sql = "UPDATE superfectaconfig SET value='".$db->escapeSimple($status)."' WHERE field = 'status' AND source ='".$db->escapeSimple($data)."'";
    sql($sql);
    $out = array("success" => true);
    break;
case "options":
    $show = FALSE;
    $scheme = str_replace("base_", "", $_REQUEST['scheme']);

    $source = $_REQUEST['source'];

    $sql = "SELECT * FROM superfectaconfig WHERE source = '".$scheme . "_" . $source."'";
    $settings = sql($sql, 'getAll');

    foreach($settings as $data) {
        $n_settings[$data[1]] = $data[2];
    }

    $path = dirname(dirname(__FILE__));
    require_once($path.'/sources/source-'.$_REQUEST['source'].'.module');
    $module = new $_REQUEST['source'];
    $params = $module->source_param;

    $title = str_replace('_', ' ', $_REQUEST['source']);
    $form_html = '<h3>'.$title.'</h3><form id="form_options_'.$_REQUEST['source'].'" action="config.php?quietmode=1&handler=file&module=superfecta&file=ajax.html.php&type=save_options&scheme='.$_REQUEST['scheme'].'&source='.$_REQUEST['source'].'" method="post">';
    $form_html .= '<table>';
    foreach($params as $key => $data) {
        $form_html .= '<tr>';
        $show = TRUE;
        $default = isset($data['default']) ? $data['default'] : '';
        switch($data['type']) {
        case "text":
            $value = isset($n_settings[$key]) ? $n_settings[$key] : $default;
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></a></td><td><input type="text" name="'.$key.'" value="'.$value.'" size="50"/></td>';
            break;
        case "password":
            $value = isset($n_settings[$key]) ? $n_settings[$key] : $default;
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></td><td><input type="password" name="'.$key.'" value="'.$value.'" size="50"/></td>';
            break;
        case "checkbox":
            $checked = isset($n_settings[$key]) && ($n_settings[$key] == 'on') ? 'checked' : $default; 
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></td><td><input type="checkbox" name="'.$key.'" value="on" '.$checked.'/></td>';
            break;
        case "textarea":
            $value = isset($n_settings[$key]) ? $n_settings[$key] : $default;
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></td><td><textarea name="'.$key.'" rows="4" cols="50">'.$value.'</textarea></td>';
            break;
        case "number":
            $value = isset($n_settings[$key]) ? $n_settings[$key] : $default;
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></td><td><input type="number" name="'.$key.'" value="'.$value.'" /></td>';
            break;
        case "select":
            $value = isset($n_settings[$key]) ? $n_settings[$key] : $default;
            $form_html .= '<td>'.str_replace("_", " ", $key).'<a class="info"><span>'.$data['description'].'</span></td><td><select name="'.$key.'">';
            foreach($data['option'] as $options_k => $options_l) {
                $selected = ($value == $options_k) ? 'selected' : '';
                $form_html .= "<option value=".$options_k." ".$selected.">".$options_l."</option>";
            }
            $form_html .= "</select></td>";
            break;
        default:
            $form_html .= '<td colspan="2"><span class="superfecta_message">WARN: Unrecognized option \''.$data['type'].'\'</span></td>';
            break;
        }
        $form_html .= '</tr>';
    }
    $form_html .= '</table>';
    $form_html .= '<input type="submit" value="Submit" /></form>';

    $out = array("success" => true, "show" => $show, "data" => $form_html);
    break;
case "save_options":
    $path = dirname(dirname(__FILE__));
    require_once($path.'/sources/source-'.$_REQUEST['source'].'.module');
    $module = new $_REQUEST['source'];
    $params = $module->source_param;

    $scheme = str_replace("base_", "", $_REQUEST['scheme']);
    $source = $_REQUEST['source'];

    foreach($params as $key => $data) {
        if(isset($_REQUEST[$key])) {
            $sql = "REPLACE INTO superfectaconfig (source,field,value) VALUES ('".$scheme . "_" . $source."', '".$key."', '".$_REQUEST[$key]."')";
            sql($sql);
        } else {
            $sql = "DELETE FROM superfectaconfig WHERE source = '".$scheme . "_" . $source."' AND field = '".$key."'";
            sql($sql);
        }
    }
    break;
}

echo json_encode($out);
