<?php

if (!function_exists('mailer')) {
    /**
     * Create and send an email
     * 
     * *Note that `\Leaf\Mail\Mailer` should be configured*
     * 
     * @param array $mail
     * 
     * @return \Leaf\Mail
     */
    function mailer($mail = null)
    {
        return new \Leaf\Mail($mail);
    }
}
