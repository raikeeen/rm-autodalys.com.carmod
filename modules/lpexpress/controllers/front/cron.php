<?php

class LPExpressCronModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        $token = Tools::getValue('token');

        if ($token != Configuration::get('LP_CRON_TOKEN'))
        {
            $this->module->logger->error('Attempt launch cron without correct token.');
            die('Invalid token!');
        }

        if (!Configuration::get('LP_API_CONNECTED'))
        {
            $this->module->logger->error('Attempt launch cron without authentication information.');
            die('Fill authentication fields first.');
        }
    }

    public function initContent()
    {
        $this->module->logger->error('Started terminal update.');

        $errors = $this->module->updateTerminals();

        if ($errors)
        {
            $this->module->logger->error('Error while updating terminals.', ['errors' => $errors]);
        }

        $this->module->logger->error('Finished terminal update.');
        die('Successfully updated terminal list.');
    }

}