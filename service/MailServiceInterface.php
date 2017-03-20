<?php

namespace Service;

/**
 *
 * @author Piotr Bernad
 */

interface MailService 
{
    public function sendEmailMessage($recipient, $msg);
}