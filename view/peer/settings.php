<?php
/**
 * @var $settings []
 * @var $settingsPeer []
 * @var $settingsWeb []
 */
?>
Настройки:
<?php foreach($settings as $set_id => $setting):?>
<?=$set_id?>) <?=$setting['title']?><?=PHP_EOL?>
    - <?php if($setting['type'] == 'boolean')
    {
        if(isset($settingsPeer[$set_id]) && $settingsPeer[$set_id] != '')
            echo ($settingsPeer[$set_id] == 1)?'Да':'Нет';
        elseif(isset($settingsWeb[$set_id]) && $settingsWeb[$set_id] != '')
            echo ($settingsWeb[$set_id] == 1)?'Да':'Нет';
        else
            echo ($setting['default'] == 1)?'Да':'Нет';
    }
    else
    {
        if(isset($settingsPeer[$set_id]) && $settingsPeer[$set_id] != '')
            echo $settingsPeer[$set_id];
        elseif(isset($settingsWeb[$set_id]) && $settingsWeb[$set_id] != '')
            echo $settingsWeb[$set_id];
        elseif(isset($settingsWeb[$set_id]) && $settingsWeb[$set_id] != '')
            echo $settingsWeb[$set_id];
        else
            echo $setting['default'];
    } ?><?=PHP_EOL?>
<?php endforeach;?>
