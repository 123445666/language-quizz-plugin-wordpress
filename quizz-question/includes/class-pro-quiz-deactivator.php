<?php

class Pro_Quiz_Deactivator
{

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

}
