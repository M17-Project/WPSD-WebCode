<?php
session_set_cookie_params(0, "/");
session_name("WPSD_Session");
session_id('wpsdsession');
session_start();

require_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/version.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/ircddblocal.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/mmdvmhost/functions.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/config/language.php';
unset($_SESSION['WPSDdashConfig']);
unset($_SESSION['MMDVMHostConfigs']);
checkSessionValidity();

$displayType = getConfigItem("General", "Display", $_SESSION['MMDVMHostConfigs']);

$themes_filepath = $_SERVER['DOCUMENT_ROOT'].'/includes/wpsd-themes.json';
$themes = [];

if (file_exists($themes_filepath)) {
    $json_content = file_get_contents($themes_filepath);
    $decoded_themes = json_decode($json_content, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_themes)) {
        $themes = $decoded_themes;
    } else {
        error_log("WPSD Dashboard: Error decoding wpsd-themes.json or it's not a valid array.");
        // Fallback to an empty array if JSON is invalid
        $themes = [];
    }
} else {
    error_log("WPSD Dashboard: wpsd-themes.json not found at " . $themes_filepath);
    // Fallback to an empty array if file doesn't exist
    $themes = [];
}

// Define the classic theme key as it's used for reset functionality
$classic_theme_key = 'WPSD Classic';

$filepath_ini = '/etc/wpsd-css.ini';
$parsed_ini = null;
$use_classic_default_for_ini_file = false;

if (file_exists($filepath_ini)) {
    $parsed_ini_content = parse_ini_file($filepath_ini, true);
    if ($parsed_ini_content === false || empty($parsed_ini_content)) {
        error_log("WPSD Dashboard: /etc/wpsd-css.ini is unparseable or empty. Using classic theme as default and attempting to recreate file.");
        $use_classic_default_for_ini_file = true;
    } else {
        $parsed_ini = $parsed_ini_content;
    }
} else {
    error_log("WPSD Dashboard: /etc/wpsd-css.ini not found. Using classic theme as default and creating file.");
    $use_classic_default_for_ini_file = true;
}

if ($use_classic_default_for_ini_file) {
    if (isset($themes[$classic_theme_key])) { // Use the friendly classic theme key
        $parsed_ini = $themes[$classic_theme_key];

        $content = "";
        foreach ($themes[$classic_theme_key] as $section => $values) {
            if (!is_array($values)) continue;
            $content .= "[" . $section . "]\n";
            foreach ($values as $key => $value) {
                $content .= $key . "=" . $value . "\n";
            }
            $content .= "\n";
        }

        $temp_ini_path = "/tmp/wpsd_default_classic.ini";
        if (file_put_contents($temp_ini_path, $content) !== false) {
            exec('sudo cp ' . escapeshellarg($temp_ini_path) . ' ' . escapeshellarg($filepath_ini));
            exec('sudo chmod 644 ' . escapeshellarg($filepath_ini));
            exec('sudo chown root:root ' . escapeshellarg($filepath_ini));
            error_log("WPSD Dashboard: /etc/wpsd-css.ini has been created/overwritten with classic theme values.");
        } else {
            error_log("WPSD Dashboard: Failed to write temporary INI file for classic default at " . $temp_ini_path);
        }
    } else {
        error_log("WPSD Dashboard: CRITICAL - '{$classic_theme_key}' theme is not defined in \$themes array. Cannot set default INI.");
        $parsed_ini = [];
    }
}

if (!is_array($parsed_ini)) {
    error_log("WPSD Dashboard: \$parsed_ini could not be initialized from file or classic theme. Defaulting to empty array.");
    if(isset($themes[$classic_theme_key])) { // Use the friendly classic theme key
        $parsed_ini = $themes[$classic_theme_key];
    } else {
        $parsed_ini = [];
    }
}


function compare_color_settings($config_colors, $theme_colors) {
    if (!is_array($config_colors) || !is_array($theme_colors)) {
        return is_array($config_colors) === is_array($theme_colors) && empty($config_colors) && empty($theme_colors);
    }
    if (count($config_colors) !== count($theme_colors)) {
        return false;
    }
    return empty(array_diff_assoc($config_colors, $theme_colors));
}

$ini_background_colors = isset($parsed_ini['Background']) ? $parsed_ini['Background'] : [];
$ini_text_colors = isset($parsed_ini['Text']) ? $parsed_ini['Text'] : [];
$selected_theme_on_load = "custom";

foreach ($themes as $theme_key => $theme_data) {
    $theme_background_colors = isset($theme_data['Background']) ? $theme_data['Background'] : [];
    $theme_text_colors = isset($theme_data['Text']) ? $theme_data['Text'] : [];

    if (compare_color_settings($ini_background_colors, $theme_background_colors) &&
        compare_color_settings($ini_text_colors, $theme_text_colors)) {
        $selected_theme_on_load = $theme_key;
        break;
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
    <head>
        <meta name="language" content="English" />
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="pragma" content="no-cache" />
        <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
        <meta http-equiv="Expires" content="0" />
        <title>WPSD Dashboard - Appearance Settings</title>
        <script type="text/javascript" src="/js/jquery.min.js?version=<?php echo $versionCmd; ?>"></script>
        <script type="text/javascript" src="/css/farbtastic/farbtastic.min.js?version=<?php echo $versionCmd; ?>"></script>
        <link rel="stylesheet" type="text/css" href="/css/farbtastic/farbtastic.css" />
        <link rel="stylesheet" type="text/css" href="/css/font-awesome-4.7.0/css/font-awesome.min.css" />
<?php include_once $_SERVER['DOCUMENT_ROOT'].'/config/browserdetect.php'; ?>
        <style type="text/css" media="screen">
            .colorwell {
                border: 2px solid #fff;
                width: 6em;
                text-align: center;
                cursor: pointer;
            }
            body .colorwell-selected {
                border: 2px solid #000;
                font-weight: bold;
            }
        </style>
        <script type="text/javascript">
            const WPSD_THEMES = <?php echo json_encode($themes); ?>;
            const WPSD_CURRENT_CONFIG_FROM_INI = <?php echo json_encode($parsed_ini); ?>;
            const INITIAL_SELECTED_THEME_KEY = <?php echo json_encode($selected_theme_on_load); ?>;

            function cssDownload() {
                window.location.href = "/admin/advanced/css_download.php";
            }

            function cssUpload() {
                document.getElementById('fileid').addEventListener('change', submitForm);
                document.getElementById('fileid').click();
            }

            function submitForm() {
                document.getElementById('cssUpload').submit();
            }

            function cssReset() {
                // Use the friendly name for the classic theme for reset
                if (confirm('WARNING: This will reset all appearance settings to the "WPSD Classic" theme and apply them. Your current unsaved customizations will be lost.\n\nAre you SURE you want to do this?\n\nPress OK to restore and apply the Classic theme.\nPress Cancel to go back.')) {
                    $('#themeSelector').val('WPSD Classic').triggerHandler('change'); // Use the friendly name
                    setTimeout(function() {
                        document.forms['edit-css'].submit();
                    }, 100);
                } else {
                    return false;
                }
            }

            $(document).ready(function() {
                var f = $.farbtastic('#colorpicker');
                var p = $('#colorpicker').css('opacity', 1).hide();
                var selected;

                function getContrastColor(hexcolor){
                    if (!hexcolor || typeof hexcolor !== 'string' || hexcolor.toLowerCase() === 'none') return '#000000'; 
                    let hex = hexcolor.replace("#", "");
                    if (hex.length === 3) {
                        hex = hex.split('').map(char => char + char).join('');
                    }
                    if (hex.length !== 6) return '#000000'; 

                    const r = parseInt(hex.substr(0,2),16);
                    const g = parseInt(hex.substr(2,2),16);
                    const b = parseInt(hex.substr(4,2),16);
                    if (isNaN(r) || isNaN(g) || isNaN(b)) return '#000000'; 

                    const yiq = ((r*299)+(g*587)+(b*114))/1000;
                    return (yiq >= 128) ? '#000000' : '#FFFFFF';
                }

                $('.colorwell')
                    .each(function () {
                        $(this).css('background-color', $(this).val()); 
                        $(this).css('color', getContrastColor($(this).val())); 
                        $(this).css('opacity', 1);
                    })
                    .focus(function() {
                        if (selected) {
                            $(selected).removeClass('colorwell-selected');
                        }
                        f.linkTo(this);
                        p.show();
                        selected = this;
                        $(this).addClass('colorwell-selected');
                    })
                    .on('change keyup input', function() { 
                        $(this).css('background-color', $(this).val()); 
                        $(this).css('color', getContrastColor($(this).val())); 
                    });

                $(document).mousedown(function(event) {
                    if (!$(event.target).closest('#colorpicker').length && !$(event.target).is('.colorwell')) {
                        if (p.is(":visible")) {
                            p.hide();
                            if (selected) {
                                $(selected).removeClass('colorwell-selected');
                                selected = null;
                            }
                        }
                    }
                });
                
                $('#themeSelector').val(INITIAL_SELECTED_THEME_KEY);

                $('#themeSelector').change(function() {
                    const selectedThemeName = $(this).val();
                    let sourceData;
                    let isPredefinedTheme = false;

                    if (selectedThemeName === 'custom') {
                        sourceData = WPSD_CURRENT_CONFIG_FROM_INI;
                        isPredefinedTheme = false;
                    } else if (selectedThemeName && WPSD_THEMES[selectedThemeName]) {
                        sourceData = WPSD_THEMES[selectedThemeName];
                        isPredefinedTheme = true;
                    } else {
                        return; 
                    }
                   
                    for (const section in sourceData) {
                       if (sourceData.hasOwnProperty(section)) {
                           for (const key in sourceData[section]) {
                               if (sourceData[section].hasOwnProperty(key)) {
                                   const inputName = section + '[' + key + ']';
                                   const inputValue = sourceData[section][key];
                                   const inputElement = $('input[name="' + inputName + '"]');

                                   if (inputElement.length) {
                                       inputElement.val(inputValue);
                                       if (inputElement.hasClass('colorwell')) {
                                           inputElement.css('background-color', inputValue);
                                           inputElement.css('color', getContrastColor(inputValue));
                                           if (selected && inputElement.is(selected)) {
                                               f.setColor(inputValue);
                                           }
                                       }
                                       if (isPredefinedTheme) {
                                           inputElement.triggerHandler('change'); 
                                       }
                                   }
                               }
                           }
                       }
                    }
                });

                if (INITIAL_SELECTED_THEME_KEY !== "" && INITIAL_SELECTED_THEME_KEY !== null) {
                   $('#themeSelector').triggerHandler('change');
                }
            });
        </script>
    </head>
    <body>
        <div class="container">
            <?php include $_SERVER['DOCUMENT_ROOT'].'/admin/advanced/header-menu.inc'; ?>
            <div class="contentwide">
                <?php
                $filepath = '/tmp/bW1kd4jg6b3N0DQo.tmp';

                if (empty($_POST['CallLookupProvider']) != TRUE) {
                    exec('sudo sed -i "/CallLookupProvider = /c\\\CallLookupProvider = '.escapeshellcmd($_POST['CallProvider']).'" ' . $config_file . '');    
                    unset($_POST);
                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
                    die();
                }
                if (isset($_POST['phoneticCallsigns'])) {
                    $phoneticCallsigns = escapeshellcmd($_POST['phoneticCallsigns']);
                    $output = shell_exec("grep -c '^PhoneticCallsigns =' $config_file");
                    if (trim($output) == '0') {
                        exec("echo 'PhoneticCallsigns = $phoneticCallsigns' | sudo tee -a $config_file > /dev/null");
                    } else {
                        exec("sudo sed -i '/PhoneticCallsigns = /c\\PhoneticCallsigns = $phoneticCallsigns' $config_file");
                    }
                    unset($_POST);
                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;},0);</script>';
                    die();
                }

                if (file_exists($filepath_ini)) {
                    exec('sudo cp '.$filepath_ini.' '.$filepath);
                    exec('sudo chown www-data:www-data '.$filepath);
                    exec('sudo chmod 664 '.$filepath);
                }

                if($_POST) {
                    $data = $_POST;
                    if (empty($_POST['cssDownload']) != TRUE) {
                    } else if (empty($_POST['cssUpload']) != TRUE) {
                        echo "<tr><th colspan=\"2\">Appearance upload and apply...</th></tr>\n";
                        if (isset($_FILES['cssFile']) && $_FILES['cssFile']['error'] === UPLOAD_ERR_OK) {
                            $output = "Uploading your appearance settings data.\n";
                            $target_dir = "/tmp/css_restore/";
                            $okay = false;
                            shell_exec("sudo rm -rf $target_dir 2>&1");
                            shell_exec("mkdir $target_dir 2>&1");
                            if($_FILES["cssFile"]["name"]) {
                                $filename = $_FILES["cssFile"]["name"];
                                $source = $_FILES["cssFile"]["tmp_name"];
                                $type = $_FILES["cssFile"]["type"];
                                $name = explode(".", $filename);
                                $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
                                foreach($accepted_types as $mime_type) {
                                    if($mime_type == $type) {
                                        $okay = true;
                                        break;
                                    }
                                }
                            }
                            $continue = false;
                            if (isset($name)) {
                                $continue = strtolower($name[1]) == 'zip' ? true : false;
                            }
                            if ($okay == false || $continue == false) {
                                $output .= "The file you are trying to upload is not a .zip file. Please try again.\n";
                                echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
                            } else {
                                if (isset($filename)) {
                                    $target_path = $target_dir.$filename;
                                }
                                if(isset($target_path) && move_uploaded_file($source, $target_path)) {
                                    $zip = new ZipArchive();
                                    $x = $zip->open($target_path);
                                    if ($x === true) {
                                        $zip->extractTo($target_dir);
                                        $zip->close();
                                        unlink($target_path);
                                    }
                                    $output .= "Your .zip file was uploaded and unpacked.\n";
                                    $output .= "Copying appearance setttings...\n";
                                    $output .= shell_exec("sudo mv -v -f /tmp/css_restore/wpsd-css.ini ".$filepath_ini." 2>&1")."\n";
                                    $output .= "Appearance Restoration Complete.\n";
                                    echo '<script type="text/javascript">setTimeout(function() { window.location=window.location;}, 4000);</script>';
                                } else {
                                    $output .= "There was a problem with the upload. Please try again.<br />";
                                    $output .= "\n".'<button onclick="goBack()">Go Back</button><br />'."\n";
                                    $output .= '<script>'."\n";
                                    $output .= 'function goBack() {'."\n";
                                    $output .= '    window.history.back();'."\n";
                                    $output .= '}'."\n";
                                    $output .= '</script>'."\n";
                                }
                                echo "<tr><td align=\"left\"><pre>$output</pre></td></tr>\n";
                            }
                        } else {
                            echo "<tr><td align=\"left\"><pre>No file uploaded or an error occurred.</pre></td></tr>\n";
                        }
                    } else {
                        if (update_ini_file($data, $filepath)) {
                            exec('sudo cp '.$filepath.' '.$filepath_ini);
                            exec('sudo chmod 644 '.$filepath_ini);
                            exec('sudo chown root:root '.$filepath_ini);
                            echo '<script type="text/javascript">window.location=window.location.href.split("?")[0];</script>';
                            die();
                        } else {
                            echo "Error updating INI file."; 
                        }
                    }
                }

                function update_ini_file($data, $filepath_to_update) {
                    $content = "";
                    foreach($data as $section=>$values) {
                        if (!is_array($values)) continue;
                        // Replace spaces with underscores for INI section names if needed for consistency with form input names
                        $section_for_ini = str_replace(" ", "_", $section);
                        $content .= "[".$section_for_ini."]\n";
                        foreach($values as $key=>$value) {
                            if ($value == '') {
                                $content .= $key."=none\n";
                            } else {
                                $content .= $key."=".$value."\n";
                            }
                        }
                        $content .= "\n";
                    }
                    if (!$handle = fopen($filepath_to_update, 'w')) {
                        return false;
                    }
                    $success = fwrite($handle, $content);
                    fclose($handle);
                    return $success;
                }
                ?>

                <?php if ($displayType == "OLED") { ?>
                <h2 class="ConfSec">OLED Display Control</h2>
                <script>
                    function toggleOLED() {
                        var xhr = new XMLHttpRequest();
                        xhr.open('GET', '/admin/OLED_ajax.php?action=toggle', true);
                        xhr.send();
                    }
                </script>
                <table>
                    <tr>
                        <td class="left">
                            <button id="toggleButton" onclick="toggleOLED()">Toggle OLED Display Off/On</button>
                        </td>
                    </tr>
                </table>
                <?php } ?>

                <br />

                <h2 class="ConfSec">Callsign Link Provider</h2>    
                <table>
                    <tr>
                        <td>
                            <form method="post" action="" class="left">
                                <input type="radio" name="CallProvider" value="RadioID" id="RadioID" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider']) && $_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider'] == "RadioID") {  echo 'checked="checked"'; } ?> />
                                <label for="RadioID">RadioID</label>
                                &nbsp;
                                <input type="radio" name="CallProvider" value="QRZ" id="QRZ" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider']) && $_SESSION['WPSDdashConfig']['WPSD']['CallLookupProvider'] == "QRZ") {  echo 'checked="checked"'; } ?> />
                                <label for="QRZ">QRZ</label>
                                &nbsp;
                                <input name="CallLookupProvider" type="submit" value="Apply Change" />
                            </form>
                        </td>
                    </tr>
                </table>
                <br>
                <h2 class="ConfSec">Phonetic Callsigns</h2>
                <table>
                    <tr>
                        <td>
                            <form method="post" action="" class="left">
                                <input type="radio" name="phoneticCallsigns" value="0" id="phoneticCallsign-false" <?php if (!isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) || (isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) && $_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns'] == "0")) {  echo 'checked="checked"'; } ?> />
                                <label for="phoneticCallsign-false">Disabled</label>
                                &nbsp;
                                <input type="radio" name="phoneticCallsigns" value="1" id="phoneticCallsign-true" <?php if (isset($_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns']) && $_SESSION['WPSDdashConfig']['WPSD']['PhoneticCallsigns'] == "1") {  echo 'checked="checked"'; } ?> />
                                <label for="phoneticCallsign-true">Enabled</label>
                                &nbsp;
                                <input name="phoneticCallsignsSubmit" type="submit" value="Apply Change" />
                            </form>
                        </td>
                        <td align="left" style='word-wrap: break-word;white-space: normal;padding-left: 5px;'><i class="fa fa-question-circle"></i> When enabled an additional label will be displayed with the phonetic version of callsigns</td>
                    </tr>
                </table>    
                <br />
    
                <h2 class="ConfSec">Appearance and Extra Look/Feel Settings</h2>
                <?php
                    echo '<form action="" method="post" name="edit-css">'."\n"; 
                ?>
                <table>
                    <tr>
                        <th class="larger" colspan="3">Themes</th> 
                    </tr>
                    <tr>
                        <td align="right" style='padding-left:10em;width:150px;'>Select Theme:</td>
                        <td align="left">
                            <select id="themeSelector">
                                <option value="" disabled>-- Select a Theme --</option>
                                <?php
                                // Populate options directly from $themes array keys (which are friendly names)
                                if ($selected_theme_on_load === 'custom') {
                                    echo '<option value="custom" selected>Custom Configuration</option>';
                                }

                                foreach ($themes as $key => $theme_data_loop):
                                    $selected_attr = ($selected_theme_on_load === $key) ? 'selected' : '';
                                    echo '<option value="' . htmlspecialchars($key) . '" ' . $selected_attr . '>' . htmlspecialchars($key) . '</option>';
                                endforeach;
                                ?>
                            </select>
                        </td>
                        <td align="left" style='word-wrap: break-word;white-space: normal;padding-left: 5px;'><i class="fa fa-info-circle"></i> Hint: You can apply the themes as-is, and/or customize them further below.</td>
                    </tr>
                    <tr>
                        <td></td> 
                        <td align="left" colspan="2" style="padding-top: 10px;">
                            <?php echo '<input type="submit" value="'.__( 'Apply Theme' ).'" />'."\n"; ?>
                        </td>
                    </tr>
                </table>

                <h3 class="ConfSec">Customize Theme</h3>

                <?php
                echo '<div style="position: fixed; pointer-events: none; transform: translateX(230%);" >'."\n";
                echo '    <div id="colorpicker" style="float: right; margin: 20px; pointer-events: auto;"></div>'."\n";
                echo '</div>'."\n";
                
                foreach($parsed_ini as $section=>$values_in_section) {
                    echo "<table>\n";
                    echo "    <tr><th class='larger' colspan=\"3\">".htmlspecialchars($section)."</th></tr>\n";
                    if (is_array($values_in_section)) {
                        foreach($values_in_section as $key=>$value) {
                            $key_display = htmlspecialchars($key);
                            $value_display = htmlspecialchars($value);
                            // Ensure section name for input matches the INI format (underscores for spaces)
                            $section_name_for_input = htmlspecialchars(str_replace(" ", "_", $section));
                            $key_name_for_input = htmlspecialchars($key);

                            if (endsWith($key, 'SectionColor')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the small section heading font color; default is \"#000000\" [black].)</td></tr>\n";
                            } elseif ($key == 'TextColor') {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Main Content font color, used across most of the Dashboard's informational/data text; default is \"#000000\" [black].)</td></tr>\n";
                            } elseif (endsWith($key, 'Color')) { 
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\" colspan='2'><input type=\"text\" class=\"colorwell\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td></tr>\n";
                            } elseif (startsWith($key, 'MainFontSize')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Main Content font size, in pixels, used across most of the Dashboard's informational/data text; default is 18 pixels.)</td></tr>\n";
                            } elseif (startsWith($key, 'BodyFontSize')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Body font size, in pixels, used across most of the Dashboard's non-data/non-informational text; default is 17 pixels.)</td></tr>\n";
                            } elseif (startsWith($key, 'HeaderFont')) {
                                echo "    <tr><td align=\"right\" style='padding-left:10em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='2' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(the Header font size, in pixels; default is 34 pixels.)</td></tr>\n";
                            } elseif (endsWith($key, 'HeardRows')) {
                                echo "    <tr><td align=\"right\" style='padding-left:15em;width:150px;'>$key_display</td><td align=\"left\"><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" size='3' maxlength='3' /></td><td align='left' style='word-wrap: break-word;white-space: normal;'>(The number of rows displayed on the Dashboard; default is 40 rows, and 100 rows is the maximum allowed.*)</td></tr>\n";
                            } else {
                                echo "    <tr><td align=\"right\" style='padding-left:15em;width:150px;'>$key_display</td><td align=\"left\" colspan='2'><input type=\"text\" name=\"{$section_name_for_input}[{$key_name_for_input}]\" value=\"$value_display\" /></td></tr>\n";
                            }
                        }
                    }
                    echo "    <tr>\n";
                    echo "        <td></td>\n"; 
                    echo "        <td colspan=\"2\" align=\"left\" style=\"padding-top: 10px;\">\n";
                    echo "            <input type=\"submit\" value=\"".__( 'Apply Changes' )."\" />\n";
                    echo "        </td>\n";
                    echo "    </tr>\n";
                    echo "</table>\n";
                    echo "<br />\n"; 
                }
                echo "</form>\n";
                echo "<p> * Because of the way MMDVMHost logs last heard data, it is not guaranteed that the number of rows specified will be displayed.</p>\n";
                echo "<hr />\n";
                echo '<form id="cssUpload" action="" method="POST" enctype="multipart/form-data">'."\n";
                echo '    <div><input id="fileid" name="cssFile" type="file" hidden/></div>'."\n";
                echo '    <div><input type="hidden" name="cssUpload" value="1" /></div>'."\n";
                echo '</form>'."\n";
                echo '<input type="button" onclick="javascript:cssDownload();" value="Download Appearance Settings" />'."\n";
                echo '<input type="button" onclick="javascript:cssUpload();" value="Upload &amp; Apply Appearance Settings (zip file only!)" />'."\n";
                echo '<input style="background:crimson;color:white;" type="button" onclick="cssReset();" value="Reset to WPSD Classic Theme" />'."\n";
                echo "<br />\n";
                echo "<br />\n";
                ?>
            </div>
            <?php include $_SERVER['DOCUMENT_ROOT'].'/includes/footer.php'; ?> 
        </div>
    </body>
</html>

