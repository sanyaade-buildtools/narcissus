<?
/* Narcissus - Online image builder for the angstrom distribution
 * Koen Kooi (c) 2008 - all rights reserved 
 *
 * basic operation:
 * 1) select machine and assemble arch.conf
 * 2) select package set and print to file
 * 3) have daemon prime rootfs with opkg-collateral and angstrom-feed-configs
 * 4) have daemon install package set
 * 5) have daemon tar it up
 */


$base_pkg_set = " task-base angstrom-version ";

if (isset($_POST["action"]) && $_POST["action"] != "") {
	$action = $_POST["action"];
} else {
	print "Invalid action: $action";
	exit;
}

if (isset($_POST["machine"])) {
	$machine = escapeshellcmd(basename($_POST["machine"]));
} else {
	print "Invalid machine";
	exit;
}

if (isset($_POST["name"]) && $_POST["name"] != "") {
	$name = escapeshellcmd(basename($_POST["name"]));
} else {
	$name = "unnamed";
}

if (isset($_POST["pkgs"]) && $_POST["pkgs"] != "") {
		$pkg = $_POST["pkgs"];
} else {
    $pkg = "task-boot";
}

switch($action) {
case "assemble_image":
			print "assembling\n";
			assemble_image($machine, $name);
			break;
case "configure_image":
			print "configuring\n";
			configure_image($machine, $name);
			break;
case "show_image_link":
			show_image_link($machine, $name);
			break;
case "install_package":
			print "installing $pkg\n";
			install_package($machine, $name, $pkg);
			break;
}


function show_image_link($machine, $name) {
	if (file_exists("deploy/$machine/$name-image-$machine.tar.bz2")) {
		$randomname = substr(md5(time()), 0, 6);
		mkdir("deploy/$machine/$randomname");
		rename("deploy/$machine/$name-image-$machine.tar.bz2", "deploy/$machine/$randomname/$name-image-$machine.tar.bz2");	
		$imgsize = round(filesize("deploy/$machine/$randomname/$name-image-$machine.tar.bz2") / (1024 * 1024),2);
		print "<br>Click to download <a href='deploy/$machine/$randomname/$name-image-$machine.tar.bz2'>your $name image for $machine</a> [$imgsize MiB]\n<br/><br/>This will get automatically deleted after 7 days.";
	} else {
		print "Image not found, something went wrong :/";
	}
}

function configure_image($machine, $name) {
	print "<pre>";
	print "Machine: $machine, name: $name\n";
	passthru ("scripts/configure-image.sh $machine $name-image && exit");
	print "</pre>";
}

function install_package($machine, $name, $pkg) {
	print "<pre>";
	print "Machine: $machine, name: $name, pkg: $pkg\n";
	passthru ("scripts/install-package.sh $machine $name-image $pkg && exit", $installretval);
	print "</pre>";
    print "<div id=\"retval\">$installretval</div>";
}

function assemble_image($machine, $name) {
	print "<pre>";
	print "Machine: $machine, name: $name\n";
	passthru ("fakeroot scripts/assemble-image.sh $machine $name-image && exit");
	print "</pre>";
}



?>
