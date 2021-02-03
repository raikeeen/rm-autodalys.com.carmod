<link rel="stylesheet" href="/<?=$ModDir?>/add/askprice/templates/default/style.css"/>

<div id="CmAskFormBl">
    <div id="CmLoadImg"></div>
    <div class="CmAskTitleBl CmColorBgL CmColorBr"><div class="CmRequestTitle CmColorTx"><?=$ReplArt?></div><div class="CmReqTitText"><?=$title?></div></div>
    <?if($_POST['Subreq']=="Y" && !$errors){ ?>
        <div class="ErrorMes">
            <?if($mailTrue){?>
                <span style="font-weight:bold; color:red; display:block;"><?=$thanks_we_will_reply?></span>
            <?}else{?>
                <span style="font-weight:bold; color:red; display:block;"><?=$errorSend?></span>
            <?}?>
        </div>
    <?}?>
    <?if($_POST['Subreq']!="Y" || $errors){?>
        <div class="CmFormBlock" id="formReq">
            <div class="CmAskPrWrap">
                <div class="CmInpBlock CmAskEmailWrap">
                    <div class="CmFormText"><?=$your_mail?></div>
                    <div class="CmAskLabInpt">
                        <input class="CmAskChechEr CmReqInput CmTitShow c_BorderHov c_BorderFoc c_Tx" title="<b style='font-size:12px;'><?=$example?></b><br><b><?=$mail?></b>&nbsp;<?=$example_email?><br><b><?=$phone?></b>&nbsp;<?=$example_phone?>" type="text" name="Useremail" style="<?if(isset($arErrorMess['Email']) && $arErrorMess['Email'] != ''){?>border:1px solid #ff0000;<?}?>" value="<?if(!isset($arErrorMess['Email']) && $arErrorMess['Email'] == ''){echo $_POST['Email'];}?>">
                        <div class="CmErrBlock" style="<?if(isset($arErrorMess['Email']) && $arErrorMess['Email'] != ''){?>display:block;<?}?>"><svg viewBox="0 0 24 24" width='18' height='30'><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 6h3l-1 8h-1l-1-8zm1.5 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg></div>
                    </div>
                </div>
                <div class="CmInpBlock CmAskNameWrap">
                    <div class="CmFormText"><?=$your_name?></div>
                    <div class="CmAskLabInpt">
                        <input class="CmAskChechEr CmReqInput c_BorderHov c_BorderFoc c_Tx" type="text" name="Reqname" style="<?if(isset($arErrorMess['Name']) && $arErrorMess['Name'] != ''){?>border:1px solid #ff0000;<?}?>" value="<?if(!isset($arErrorMess['Name']) && $arErrorMess['Name'] == ''){echo $_POST['Name'];}?>">
                        <div class="CmErrBlock" style="<?if(isset($arErrorMess['Name']) && $arErrorMess['Name'] != ''){?>display:block;<?}?>"><svg viewBox="0 0 24 24" width='18' height='30'><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 6h3l-1 8h-1l-1-8zm1.5 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg></div>
                    </div>
                </div>
                <div class="CmInpBlock CmAskMessWrap">
                    <div class="CmFormText"><?=$message?></div>
                    <div class="CmAskLabInpt">
                        <textarea class="CmAskChechEr CmReqTextarea CmTitShow c_BorderHov c_BorderFoc c_Tx" title="<?=$textarea_title?>" type="text" name="message" style="<?if(isset($arErrorMess['Text']) && $arErrorMess['Text'] != ''){?>border:1px solid #ff0000;<?}?>" value="<?if(!isset($arErrorMess['Text']) && $arErrorMess['Text'] == ''){echo $_POST['Text'];}?>"><?=$MessArt?></textarea>
                        <div class="CmErrBlock" style="<?if(isset($arErrorMess['Text']) && $arErrorMess['Text'] != ''){?>display:block;<?}?>"><svg viewBox="0 0 24 24" width='18' height='30'><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 6h3l-1 8h-1l-1-8zm1.5 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg></div>
                    </div>
                </div>
                <div class="CmInpBlock CmAskCaptWrap">
                    <div class="CmFormText CmCaptInp"><?=$empty_code?></div>
                    <div class="CmAskLabInpt">
                        <div class="CmCaptInptBl">
                            <input class="CmAskChechEr CmCaptchaInput CmTitShow c_BorderHov c_BorderFoc c_Tx" type="text" title="<?=$captcha_title?>" name="captcha" maxlength="6" style="<?if(isset($arErrorMess['Capt']) && $arErrorMess['Capt'] != ''){?>border:1px solid #ff0000;<?}?>">
                            <div class="CmCaptImg"><img style="background-color:#e9f1f5; width:100px; height:28px;" src="/<?=$ModDir?>/add/askprice/captcha/captcha.php"/></div>
                            <div class="CmErrBlock" style="<?if(isset($arErrorMess['Capt']) && $arErrorMess['Capt'] != ''){?>display:block;<?}?>"><svg viewBox="0 0 24 24" width='18' height='30'><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm-1.5 6h3l-1 8h-1l-1-8zm1.5 12.25c-.69 0-1.25-.56-1.25-1.25s.56-1.25 1.25-1.25 1.25.56 1.25 1.25-.56 1.25-1.25 1.25z"/></svg></div>
                        </div>
                    </div>
                </div>
                <div class="CmAskSubmWrap">
                    <div id="CmAskReqSub" class="CmColorBr CmColorBgh CmColorBgL " data-artnum="<?=$Art?>" data-brand="<?=$Bra?>" data-moduledir="<?=$ModDir?>" data-lang="<?=$Lang?>" data-link="<?=$Link?>">
                        <svg class="CmAskPriceImg CmColorFi" width='18' height='30' viewBox="0 -5 24 24"><path d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm1.25 17c0 .69-.559 1.25-1.25 1.25-.689 0-1.25-.56-1.25-1.25s.561-1.25 1.25-1.25c.691 0 1.25.56 1.25 1.25zm1.393-9.998c-.608-.616-1.515-.955-2.551-.955-2.18 0-3.59 1.55-3.59 3.95h2.011c0-1.486.829-2.013 1.538-2.013.634 0 1.307.421 1.364 1.226.062.847-.39 1.277-.962 1.821-1.412 1.343-1.438 1.993-1.432 3.468h2.005c-.013-.664.03-1.203.935-2.178.677-.73 1.519-1.638 1.536-3.022.011-.924-.284-1.719-.854-2.297z"/></svg>
                        <span class="CmColorTx"><?=$askPrice?></span>
                    </div>
                </div>
            </div>
        </div>
    <?}?>
</div>
