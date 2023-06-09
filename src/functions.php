<?php

if (!function_exists('email')) {
    /**
     * Mailer
     * 
     * @param array $mail
     * 
     * @return \Leaf\Mail|\Leaf\Mail\Mailer
     */
    function email($mail = null)
    {
        return new \Leaf\Mail($mail);
    }
}
