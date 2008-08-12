<?php
/**
 * package.xml generation script
 *
 * @package MDB2_Debug_ExplainQueries
 * @author  Lars Olesen <lars@legestue.net>
 * @since   0.1.0
 * @version @package-version@
 */

if (!empty($_GET['version'])) {
    $version = $_GET['version'];
} elseif(!empty($_SERVER['argv'][2])) {
    $version = $_SERVER['argv'][2];
} else {
    $version = '0.1.1';
}

$stability = 'alpha';
$notes = '* initial release as PEAR';

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$pfm = new PEAR_PackageFileManager2();
$pfm->setOptions(
    array(
        'baseinstalldir'    => '/',
        'filelistgenerator' => 'file',
        'packagedirectory'  => dirname(__FILE__) . '/src',
        'packagefile'       => 'package.xml',
        'ignore'            => array(
            'generate_package_xml.php',
            '*.tgz'
            ),
        'exceptions'        => array(),
        'simpleoutput'      => true,
    )
);

$pfm->setPackage('MDB2_Debug_ExplainQueries');
$pfm->setSummary('Times the execution of queries and EXPLAINs');
$pfm->setDescription('Times and EXPLAINS queries with MDB2.');
$pfm->setChannel('public.intraface.dk');
$pfm->setLicense('BSD license', 'http://www.opensource.org/licenses/bsd-license.php');
$pfm->addMaintainer('lead', 'lsolesen', 'Lars Olesen', 'lars@legestue.net');

$pfm->setPackageType('php');

$pfm->setAPIVersion($version);
$pfm->setReleaseVersion($version);
$pfm->setAPIStability($stability);
$pfm->setReleaseStability($stability);
$pfm->setNotes($notes);
$pfm->addRelease();

$pfm->addGlobalReplacement('package-info', '@package-version@', 'version');

$pfm->clearDeps();
$pfm->setPhpDep('5.1.0');
$pfm->setPearinstallerDep('1.5.0');
$pfm->addPackageDepWithChannel('required', 'MDB2', 'pear.php.net', '0.18.0');

$pfm->generateContents();

if (isset($_GET['make']) || (isset($_SERVER['argv']) && @$_SERVER['argv'][1] == 'make')) {
    if ($pfm->writePackageFile()) {
        exit('file written');
    }
    else {
        exit('could not write file');
    }
} else {
    $pfm->debugPackageFile();
}
?>