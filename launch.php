<?php

define ("ROOT_FOLDER", __DIR__);

spl_autoload_register(function ($class_name) {
  $files = [
    ROOT_FOLDER."/sys/dev/$class_name.class.php",
    ROOT_FOLDER."/plt/dev/$class_name.class.php",
  ];
  foreach($files as $file) 
    if (file_exists($file)) include_once $file;
});



$dbConnection = new DbConnection();

/* * * * * * * * 
*
* 0. DATABASE RUN TIME
*
* * * * * * * */
$dbConnection->_data = json_decode(file_get_contents("db.json"),1);


/* * * * * * * * 
*
* 1. LAUNCH TIME 
*
* * * * * * * */
function launchTime() {
        global $title, $newsLbl;

                   require ROOT_FOLDER."/app/prd/launchTime.constants.php";
        $website = require ROOT_FOLDER."/app/dev/root.component.php";

        echo $website->asHtml();
        file_put_contents(ROOT_FOLDER."/app/prd/session00.bin", serialize($website));

}
/* * * * * * * * 
*
* 2. RUN TIME 
*
* * * *  * * * */
function runTime() {
        global $dbConnection;

        /* 2.1 Load from last state */
        $website = unserialize(file_get_contents(ROOT_FOLDER."/app/prd/session00.bin"));

        /* 2.2 Run Client Code */
        if (isset($_POST["commands"])) foreach($_POST["commands"] as $cmd) eval($cmd);

        /* 2.3 Sync with database */
        $website->syncWithDatabase($dbConnection);

        /* 2.5 Create UPDATE code for browser */
        echo $website->flushAndPrintUpdatesAsJs("document.documentElement");

        /* 2.6 SAVE new STATE */
        file_put_contents(ROOT_FOLDER."/app/prd/session00.bin", serialize($website));

}

if (!isset($_GET["dataShuttle"])) launchTime(); else runTime();

