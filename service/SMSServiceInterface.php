<?php

namespace Service;

/**
 *
 * @author Piotr Bernad
 */

interface SMSService 
{
    public function sendSMSMessage($to, $message);
}