<?php
/**
 * @author nieminen <nieminen432@gmail.com>
 */

use devpirates\MVC\Interfaces\LogLevels;

try {
//$start = microtime(true);
require './vendor/autoload.php';
$app = new devpirates\MVC\TemplateMVCApp("haml");

$app->Autoload("./app/controllers", array("./app/classes", "./app/models", "./app/helpers", "./app/repos"));
$logger = new HTMLCommentLogger();
$logger->SetMinLevel(LogLevels::TRACE);
$app->SetLogger($logger);

// private creds file, sets $dbServer, $dbName, $dbUser, and $dbPass variables
require "../private/mvc_db_creds.php";
// configure the templated mvc app db access
$app->Config($dbServer, $dbName, $dbUser, $dbPass);
unset ($dbServer, $dbName, $dbUser, $dbPass); // unset variables so they can't be accessed again
$app->ConfigSession("DEMOSESSION");


require "./app/classes/Constants.php";
$siteData = array(); // set site data replacer values
$siteData["SiteTitle"] = Constants::SITE_NAME;
$siteData["SiteName"] = Constants::SITE_NAME;
$siteData["Scripts"] = "";
$siteData["SiteSubtitle"] = Constants::SITE_SUBTITLE;
$siteData["CopyYear"] = date("Y");
$siteData["SiteAddress"] = Constants::SITE_ADDRESS;
$siteData["SiteDescription"] = Constants::SITE_DESCRIPTION;
$siteData["PageTitle"] = Constants::SITE_NAME;

// starts the mvc process. Passing in view directory, name of 404 controller, and default site data replacer values
$app->Start("./app/views", "FileNotFoundController", $siteData);
//echo 'Render took ' . number_format(microtime(true) - $start, 3) . ' seconds.';
} catch (Exception $e) {
    echo "MVC ERROR: " . $e->getMessage();
}
?>