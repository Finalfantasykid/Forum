<?php
require_once('commandLine.inc');

global $wgUser;
$wgUser = User::newFromName("Admin");

$queriesSoFar = 0;
global $wgDBname;
$papers = Paper::getAllPapers('all', 'all', 'both', false);
$nPapers = count($papers);
$insertSQL = "INSERT INTO `grand_product_authors`
	          (`author`, `product_id`, `order`) VALUES\n";
$inserts = array();
foreach($papers as $paper){
    $sqls = $paper->syncAuthors(true);
    foreach($sqls[1] as $s){
        $inserts[] = $s;
    }
    show_status(++$queriesSoFar, $nPapers+3);
}
DBFunctions::begin();
DBFunctions::execSQL("TRUNCATE TABLE `grand_product_authors`", true, true);
show_status(++$queriesSoFar, $nPapers+3);
DBFunctions::execSQL($insertSQL.implode(",\n",$inserts), true, true);
show_status(++$queriesSoFar, $nPapers+3);
DBFunctions::commit();
show_status(++$queriesSoFar, $nPapers+3);
?>
