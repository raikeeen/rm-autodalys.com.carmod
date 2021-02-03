<?php

class ElprestaUpdater
{
    public static function check($module)
    {
        $content = self::getNewestVersion($module);
        $new_version = Configuration::get(strtoupper($module->name.'_last_update_check_version'), $module->version);

        if (version_compare($new_version, $module->version, '>'))
        {
            $response_json = Configuration::get(strtoupper($module->name.'_last_update_check_response'), '');

            if (!empty($response_json))
            {
                $response = json_decode($response_json, true);
                $docs_url = $response['docs_url'];
                $update_url = $response['update_url'];
                $additional_text = base64_decode($response['additional_text_base64']);

                $linkToDownload = $readDocumentation = '';
                if (isset($update_url) && !empty($update_url)) {
                    $linkToDownload = $module->l('Updated module can be downloaded from').' - <a target="_blank" href="'.$update_url.'">'.$update_url.'</a>.';
                }

                if (isset($docs_url) && !empty($docs_url)) {
                    $readDocumentation = $module->l('More about update').' <a target="_blank" href="'.$docs_url.'">'.$module->l('read here').'</a>.';
                }

                $content .=  $module->displayWarning($module->l('New module update was found.').' '.$linkToDownload.' '.$readDocumentation. ' '.$additional_text);
            }
        }

        return $content;
    }

    private static function getNewestVersion($module)
    {
        try
        {
            if (Configuration::get(strtoupper($module->name . '_last_update_check_date'), 0) < (date("U") - 86400))
            {
                Configuration::updateValue(strtoupper($module->name . '_last_update_check_date'), date("U"));

                if (ini_get("allow_url_fopen"))
                {
                    if (function_exists("file_get_contents"))
                    {
                        $ctx = stream_context_create(array(
                            'http' => array(
                                'timeout' => 10,  // timeout 10 sec
                            )
                        ));

                        $response_json = @file_get_contents('http://update.elpresta.eu/get.php?m=' . $module->name . "&v=" . self::encrypt($module->version) . "&pv=" . self::encrypt(_PS_VERSION_) . "&h=" . self::encrypt(_PS_BASE_URL_ . __PS_BASE_URI__),
                            false, $ctx);

                        if (!empty($response_json))
                        {
                            $response = json_decode($response_json, true);

                            if (isset($response['new_version']) && !empty($response['new_version']))
                            {
                                Configuration::updateValue(strtoupper($module->name . '_last_update_check_version'),
                                    $response['new_version']);
                                Configuration::updateValue(strtoupper($module->name . '_last_update_check_response'),
                                    $response_json);

                                if (version_compare($module->version, $response['new_version'], '='))
                                    return $module->displayConfirmation($module->l('Successfully checked for updates. No new updates were found. You are using the latest module version.'));
                            }
                        }

                    }
                    else
                        return $module->displayWarning($module->l('Unable to check for updates. Funcion `file_get_contents` is disabled.'));
                }
                else
                    return $module->displayWarning($module->l('Unable to check for updates. Setting `allow_url_fopen` is disabled.'));
            }

            return '';
        }
        catch (Exception $ex)
        {
            Configuration::updateValue(strtoupper($module->name . '_last_update_check_date'), date("U"));
            return $module->displayWarning($module->l('Failed to check for updates. Error: '.$ex->getMessage()));
        }
    }

    public static function encrypt($input)
    {
        return strtr(base64_encode($input), '+/=', '._-');
    }
}
