<?php

//Creative Commons Attribution-ShareAlike 4.0 International License

/**
 * Lets get the session started up
 */
ob_start();
session_start();

//this helps to always know we can address the 
//$_SESSION as an array even if it is empty.
if(!isset($_SESSION)) $_SESSION = array(); 

//extract the URI
$uriArray = uriSort();

//this is where our photo albums live
$masterDir = 'photos';

//set some html templates
$loginTemplate = 'core/templates/login.html';
$albumTemplate = 'core/templates/albums.html';
$photosTemplate = 'core/templates/photos.html';
$manageTemplate = 'core/templates/manage.html';
$photosAdminTemplate = 'core/templates/photos-admin.html';

//Set an ignore list:
//this excludes names of files and folders from showing up in the gallery
$ignoreList = array('.', '..', 'list.php', 'rights', 'thumbs', 'captions', 'extra', 'movies');


$photoHighlight = '';
$htmlPage = "";
$currentAlbum = '';
$currentPhoto = '';
$usersArray = array();
$errors = "";

//set a default view
if(isset($_SESSION['logged'])) $exibeoView = 'ALBUM-SELECTION';
else $exibeoView = 'SHOW-LOGIN';


for($n = 0; $n < count($uriArray); $n++){
	if($uriArray[$n] == 'album'){
		if($uriArray[$n + 1] != ""){
			$exibeoView = 'SHOW-ALBUM';
			$currentAlbum = $uriArray[$n + 1];
		}
	}
	else if($uriArray[$n] == 'manage'){
		if(isset($_SESSION['username']) and $_SESSION['username'] == 'admin'){
			$exibeoView = 'MANAGE';
			$manageAlbum = $uriArray[$n + 1];
		}
		else $exibeoView = 'ALBUM-SELECTION';
	}
	else if($uriArray[$n] == 'users'){
		if(isset($_SESSION['username']) and $_SESSION['username'] == 'admin'){
			$exibeoView = 'USERS';
		}
		else $exibeoView = 'ALBUM-SELECTION';
	}
	else if($uriArray[$n] == 'ajax'){
		$currentAlbum = $uriArray[$n + 1];
		$currentPhoto = $uriArray[$n + 2];
		$exibeoView = 'AJAX';
	}
	else if($uriArray[$n] == 'save-caption'){
		if(isset($_SESSION['username']) and $_SESSION['username'] == 'admin'){
			$currentAlbum = $uriArray[$n + 1];
			$currentPhoto = $uriArray[$n + 2];
			$exibeoView = 'SAVE-CAPTION';
		}
	}
	else if($uriArray[$n] == 'get-caption'){
		if(isset($_SESSION['username']) and $_SESSION['username'] == 'admin'){
			$currentAlbum = $uriArray[$n + 1];
			$currentPhoto = $uriArray[$n + 2];
			$exibeoView = 'GET-CAPTION';
		}
	}
	else if($uriArray[$n] == 'logout'){
		$exibeoView = 'LOGOUT';
	}
}



if($exibeoView == 'AJAX'){
	$photoCaption = checkForCaption($currentAlbum, $currentPhoto);
	$lastPhoto = getLastPhoto($currentAlbum, $currentPhoto);
	$nextPhoto = getNextPhoto($currentAlbum, $currentPhoto);

	echo urlencode($lastPhoto . '{!}' . $nextPhoto . '{!}' . $photoCaption);
}
else if($exibeoView == 'SHOW-LOGIN'){
	if(isset($_POST['submit']) and $_POST['submit']){
		$_POST['username'] = preg_replace('/[^A-Za-z0-9_]/', '', $_POST['username']);
		$_POST['password'] = preg_replace('/[^A-Za-z0-9:\@~.,!&*()_\/\-+=?]/', '', $_POST['password']);
		if($_POST['username'] != "" and $_POST['password'] != ""){
			if($username = checkLogin($_POST['username'], $_POST['password'])){
				$_SESSION['logged'] = true;
				$_SESSION['username'] = $username;
				header('Location: /');
			}
			else $errors = "Incorrect username and/or password";
		}
		else $errors = "Please type a username and password";
	}

	$htmlPage .= file_get_contents($loginTemplate);
	$htmlPage = injection($htmlPage, 'error', $errors);
}
else if($exibeoView == 'SAVE-CAPTION'){
	$filenameArray = explode('.', $currentPhoto);
	if(!file_put_contents( dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/captions/' . $filenameArray[0] . '.txt', $_POST['caption'])){
		echo "Failed to save";
	}
	else echo $_POST['caption'];
}
else if($exibeoView == 'GET-CAPTION'){
	$filenameArray = explode('.', $currentPhoto);
	if(is_file( dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/captions/' . $filenameArray[0] . '.txt' )){
		echo file_get_contents( dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/captions/' . $filenameArray[0] . '.txt');
	}
	else echo "";
}
else if($exibeoView == 'ALBUM-SELECTION'){

	$albumHTMLTiles = "";
	$albumsArray = cleanINodes( scandir($masterDir) );

	//get the page template
	$htmlPage .= file_get_contents($albumTemplate);

	if(count($albumsArray) == 0){

		//no albums found notice
		$albumHTMLTiles .= "<div class=\"col-xs-12 col-sm-6 col-md-3 col-lg-2\">\n";
		$albumHTMLTiles .= "	<div class=\"img-thumbnail albumCover\" style=\"background-color: #DDDDDD\">\n";
		$albumHTMLTiles .= "		<h4>No Albums Found</h4><p>Please check your /{$masterDir} folder</p>\n";
		$albumHTMLTiles .= "	</div>\n";
		$albumHTMLTiles .= "</div>\n";

	}
	else{

		//If there's an list available then we need to use it
		if(is_file( dirname(__FILE__) . '/' . $masterDir . '/list.php' )){
			$listFile = file_get_contents( dirname(__FILE__) . '/' . $masterDir . '/list.php' );
			$listFileArray = explode("\r\n", $listFile);

			$albumsArrayReordered = array();
			$sectionHeaders = array();
			$i = 0;

			for($n = 0; $n < count($listFileArray); $n++){

				$sectionHeaders[$n]['html'] = "";

				if($listFileArray[$n] != "" and $listFileArray[$n] != " "){
					
					if(substr($listFileArray[$n], 0, 1) == '*'){
						$sectionHTML = substr($listFileArray[$n], 1);
						$sectionHeaders[$i]['html'] = "<div class=\"col-xs-12 col-sm-12 col-md-12 col-lg-12 sectionWrapper sectionEmbed\"><div class=\"sectionHeader\">" . $sectionHTML . "</div></div>\n";
					}
					else if(substr($listFileArray[$n], 0, 1) == '#'){
						//we ignore comments
					}
					else{

						if(in_array($listFileArray[$n], $albumsArray)){
							$albumsArrayReordered[] = $listFileArray[$n];
							$i++;
						}

					}
				}
			}

			//print_r($albumsArray);
			//print_r($albumsArrayReordered);
			//print_r($sectionHeaders);
			$albumsArray = $albumsArrayReordered;
		}

		for($n = 0; $n < count($albumsArray); $n++){

			$album = $albumsArray[$n];

			if(!in_array($album, $ignoreList)){
				$firstImage = findFirstImage($album);
			}

			if(isset($sectionHeaders[$n]) and $sectionHeaders[$n]['html'] != "") $albumHTMLTiles .= $sectionHeaders[$n]['html'];

			//build each album tile
			$albumHTMLTiles .= "<div class=\"col-xs-12 col-sm-6 col-md-3 col-lg-2\">\n";
			$albumHTMLTiles .= "	<div class=\"img-thumbnail albumCover divLink\" style=\"background-image: url('{$firstImage}')\" data-link-url=\"/album/{$album}\">\n";
			$albumHTMLTiles .= "		<a href=\"/album/{$album}\" class=\"btn btn-purple\">" . humaniseTxt($album) . "</a>\n";
			$albumHTMLTiles .= "	</div>\n";
			$albumHTMLTiles .= "</div>\n";

		}
	}

	//build menu items for admin and users
	if($_SESSION['username'] == 'admin'){
		$menuItems = "<li><a href=\"/users\">Edit Users</a></li>";
		$menuItems .= "<li><a href=\"/logout\">Logout</a></li>";
	}
	else $menuItems = "<li><a href=\"/logout\">Logout</a></li>";

	//inject the tiles and menu items into the html page
	$htmlPage = injection($htmlPage, 'tiles', $albumHTMLTiles);	
	$htmlPage = injection($htmlPage, 'menuItems', $menuItems);

}
else if($exibeoView == 'SHOW-ALBUM'){

	if(checkFolderRights($currentAlbum)){

		$photoHTMLTiles = "";
		$photosArray = cleanINodes( scandir($masterDir . '/' . $currentAlbum) );	

		//get the page template
		if($_SESSION['username'] == 'admin') $htmlPage .= file_get_contents($photosAdminTemplate);
		else $htmlPage .= file_get_contents($photosTemplate);
		
		//build the back button (first tile)
		$photoHTMLTiles .= "<div class=\"col-xs-12 col-sm-6 col-md-3 col-lg-2\">\n";
		$photoHTMLTiles .= "	<div class=\"img-thumbnail albumCover divLink\" style=\"background-color: #DDDDDD\" data-link-url=\"/\">\n";
		$photoHTMLTiles .= "		<h4><i class=\"glyphicon glyphicon-hand-left backTile\"></i><br>Back</h4>\n";
		$photoHTMLTiles .= "	</div>\n";
		$photoHTMLTiles .= "</div>\n";

		foreach($photosArray as $photo){

			//rewrites the filenames removing all illegal characters such as space!
			if($_SESSION['username'] == 'admin'){
				$checkFilename = preg_replace('/[^A-Za-z0-9.\-\_()]/', '', $photo);
				if($checkFilename != $photo){
					rename(dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/' . $photo, dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/' . $checkFilename);
				}
				$photo = $checkFilename;
			}

			if(!in_array($photo, $ignoreList)){
				
				if(is_file(dirname(__FILE__) . "/{$masterDir}/{$currentAlbum}/thumbs/{$photo}")) $photoURL = "/{$masterDir}/{$currentAlbum}/thumbs/{$photo}";
				else $photoURL = "/{$masterDir}/{$currentAlbum}/{$photo}";

				//build each album tile
				$photoHTMLTiles .= "<div class=\"col-xs-12 col-sm-6 col-md-3 col-lg-2\">\n";
				$photoHTMLTiles .= "	<div class=\"img-thumbnail albumCover\" style=\"background-image: url('{$photoURL}')\" data-toggle=\"exibeoBox\" data-master-dir=\"{$masterDir}\" data-album=\"{$currentAlbum}\" data-photo=\"{$photo}\"></div>\n";
				$photoHTMLTiles .= "</div>";
			}
		}


		if(is_dir(dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/movies')){
			$moviesArray = cleanINodes( scandir($masterDir . '/' . $currentAlbum . '/movies') );

			if(!empty($moviesArray)){
				//check for videos
				$photoHTMLTiles .= "<div class=\"col-xs-12 col-sm-12 col-md-12 col-lg-12 sectionWrapper sectionEmbed\"><div class=\"sectionHeader\"><emot class='popo-yellow-after-boom'></emot>Movies</div></div>\n";
			}

			foreach($moviesArray as $movie){

			//rewrites the filenames removing all illegal characters such as space!
			if($_SESSION['username'] == 'admin'){
				$checkFilename = preg_replace('/[^A-Za-z0-9.\-\_()]/', '', $movie);
				if($checkFilename != $movie){
					rename(dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/movies/' . $movie, dirname(__FILE__) . '/' . $masterDir . '/' . $currentAlbum . '/movies/' . $checkFilename);
				}
				$movie = $checkFilename;
			}

			$movieURL = "/{$masterDir}/{$currentAlbum}/movies/{$movie}";

			//build each album tile
			$photoHTMLTiles .= "<div class=\"col-xs-12 col-sm-6 col-md-3 col-lg-2\">\n";

			$photoHTMLTiles .= "<div class=\"img-thumbnail videoThumb embed-responsive embed-responsive-16by9\">\n";
  			$photoHTMLTiles .= "<video class=\"embed-responsive-item\" src=\"{$movieURL}\" controls></video>\n";
  			$photoHTMLTiles .= "</div>\n";
			//$photoHTMLTiles .= "	<div class=\"img-thumbnail albumCover\" style=\"background-image: url('{$movieURL}')\" data-toggle=\"exibeoBox\" data-master-dir=\"{$masterDir}\" data-album=\"{$currentAlbum}\" data-photo=\"{$movie}\"></div>\n";
			$photoHTMLTiles .= "</div>";
		}			

		}


		//build menu items for admin and users
		if($_SESSION['username'] == 'admin'){
			$menuItems = "<li><a href=\"/manage/{$currentAlbum}\">Edit Rights</a></li>";
			$menuItems .= "<li><a href=\"/\">Back</a></li>";
			$menuItems .= "<li><a href=\"/logout\">Logout</a></li>";
		}
		else{
			$menuItems = "<li><a href=\"/\">Back</a></li>";
			$menuItems .= "<li><a href=\"/logout\">Logout</a></li>";
		}

		//inject the tiles and menu items into the html page
		$htmlPage = injection($htmlPage, 'tiles', $photoHTMLTiles);	
		$htmlPage = injection($htmlPage, 'menuItems', $menuItems);
	}
}
else if($exibeoView == 'MANAGE'){
	
	if($_SESSION['username'] == 'admin'){

		if(isset($_POST['submit']) and $_POST['submit']){
			file_put_contents(dirname(__FILE__) . '/' . $masterDir . '/' . $manageAlbum . '/rights/' . 'rights.txt', $_POST['rights']);;
			header('Location: /');
		}
		else{
			if(is_file( dirname(__FILE__) . '/' . $masterDir . '/' . $manageAlbum . '/rights/' . 'rights.txt' )){
				$rightsFile = file_get_contents(dirname(__FILE__) . '/' . $masterDir . '/' . $manageAlbum . '/rights/' . 'rights.txt');
			}
			else $rightsFile = "";
		}

		$htmlPage .= file_get_contents($manageTemplate);
		$htmlPage = injection($htmlPage, 'rights', $rightsFile);
		$htmlPage = injection($htmlPage, 'error', $errors);
		$htmlPage = injection($htmlPage, 'currentAlbum', '/album/' . $manageAlbum);
	}

}
else if($exibeoView == 'USERS'){
	
	if($_SESSION['username'] == 'admin'){

		if(isset($_POST['submit']) and $_POST['submit']){
			file_put_contents(dirname(__FILE__) . '/users/logins.txt', $_POST['rights']);;
			header('Location: /');
		}
		else{
			if(is_file( dirname(__FILE__) . '/users/logins.txt' )){
				$rightsFile = file_get_contents(dirname(__FILE__) . '/users/logins.txt');
			}
			else $rightsFile = "";
		}

		$htmlPage .= file_get_contents($manageTemplate);
		$htmlPage = injection($htmlPage, 'rights', $rightsFile);
		$htmlPage = injection($htmlPage, 'error', $errors);
		$htmlPage = injection($htmlPage, 'currentAlbum', '/');
	}

}
else{
	unset($_SESSION['logged']);
	unset($_SESSION['username']);
	header('Location: /');
}




//end of logic - echo page
echo $htmlPage;





/** Functions Below **/


/**
* Function humaniseTxt is a simple function that takes a folder
* string name and outputs a cleaner English version by stripping
* or converting particular characters or words. 
* @param string $txt
* @return string
*/
function humaniseTxt($txt)
{
	$pretty = "";
	$pretty = str_replace('-', ' ', $txt);
	$pretty = ucwords($pretty);

	//common linking word lowercasing
	$pretty = str_replace('And', 'and', $pretty);
	$pretty = str_replace('Or', 'or', $pretty);
	$pretty = str_replace('Is', 'is', $pretty);
	$pretty = str_replace('It', 'it', $pretty);
	$pretty = str_replace('The', 'the', $pretty);

	//we still want the first word capitalised
	//even if it is a linking word found above
	$pretty = ucfirst($pretty);
	return $pretty;
}

function checkForCaption($album, $photo)
{
	$photoArray = explode('.', $photo);
	global $masterDir;
	$captionFormated = "";
	if(is_file(dirname(__FILE__) . '/' . $masterDir . '/' . $album . '/captions/' . $photoArray[0] . '.txt' )){
		$captionRaw = file_get_contents(dirname(__FILE__) . '/' . $masterDir . '/' . $album . '/captions/' . $photoArray[0] . '.txt');
		
		$oP = "<p class=\"caption\">";
		$cP = "</p>";

		$captionFormated = $oP . $captionRaw . $cP;
		$captionFormated = str_replace("\n", $cP . $oP, $captionFormated);
	}

	return $captionFormated;
}

function checkLogin($username, $password)
{
	$loginSuccess = false;
	$fH = fopen(dirname(__FILE__) . '/users/logins.txt', 'r');
	if($fH){
		while( !feof($fH) ){
			$fLine = fgets($fH, 4096);

			$fLine = str_replace("\r\n", "", $fLine);
			if($fLine != ""){
				if(substr($fLine, 0, 1) != '#'){
					$loginDetails = explode(' ', $fLine);

					if($loginDetails[0] == $username and $loginDetails[1] == $password){
						$loginSuccess = true;
					}
				}
			}
		}
	}
	fclose($fH);

	if($loginSuccess) return $username;
	else return false;
}

function uriSort()
{
	$requested_uri = preg_replace('/[^A-Za-z0-9:\@~.,!&*()_\/\-+=?]/', '', $_SERVER['REQUEST_URI']);
	//xss attack prevention (additional security)
	while(preg_match('/(%3C|&#x3C|&#60|PA==|&lt|&#|&#x|\x)/i', $requested_uri)){
		$requested_uri = preg_replace('/(%3C|&#x3C|&#60|PA==|&lt|&#|&#x|\x)/i', '', $requested_uri);
	}

	return $uriArray = explode('/', $requested_uri);
}

function getLastPhoto($currentAlbum, $currentPhoto)
{
	global $masterDir;
	global $ignoreList;

	$photosArray = cleanINodes( scandir($masterDir . '/' . $currentAlbum) );
	$lastPhoto = "";

	foreach($photosArray as $photo){
		if(!in_array($photo, $ignoreList)){
			if($photo == $currentPhoto) break;
			$lastPhoto = $photo;
		}
	}

	return $lastPhoto;
}

function getNextPhoto($currentAlbum, $currentPhoto)
{
	global $masterDir;
	global $ignoreList;

	$photosArray = cleanINodes( scandir($masterDir . '/' . $currentAlbum) );
	$nextPhoto = "";
	$getNext = false;

	foreach($photosArray as $photo){
		if(!in_array($photo, $ignoreList)){
			if($getNext){
				$nextPhoto = $photo;
				break;
			}
			if($photo == $currentPhoto) $getNext = true;
		}
	}

	return $nextPhoto;
}

function findFirstImage($albumName)
{
	global $masterDir;
	global $ignoreList;

	$photosArray = cleanINodes( scandir($masterDir . '/' . $albumName) );

	foreach($photosArray as $photo){
		if(!in_array($photo, $ignoreList)){

				if(is_file(dirname(__FILE__) . "/{$masterDir}/{$albumName}/thumbs/{$photo}")) return $masterDir . '/' . $albumName . '/thumbs/' . $photo;
				else return $masterDir . '/' . $albumName . '/' . $photo;
		}
	}
	
	return 'core/logos/noimage.jpg';
}

function injection($contents, $pageVar, $value)
{
	$contents = str_replace('{' . $pageVar . '}', $value, $contents);
	return $contents;
}

function cleanINodes($dirArray)
{
	global $masterDir;

	$cleanArray = array();
	foreach($dirArray as $dir){
		if($dir != '.' and $dir != '..' and $dir != '' and $dir != ' '){
			if(is_dir(dirname(__FILE__) . '/' . $masterDir . '/' . $dir)){

				if(checkFolderRights($dir)){
					$cleanArray[] = $dir;
				}
			}
			else $cleanArray[] = $dir;
		}
	}

	return $cleanArray;
}

function checkFolderRights($dir)
{
	global $masterDir;
	$inFile = false;
	if($_SESSION['username'] != 'admin'){
		if(is_file( dirname(__FILE__) . '/' . $masterDir . '/' . $dir . '/rights/' . 'rights.txt' )){
			$fH = fopen(dirname(__FILE__) . '/' . $masterDir . '/' . $dir . '/rights/' . 'rights.txt', 'r');
			if($fH){
				while( !feof($fH) ){

					$fLine = fgets($fH, 4096);

					$fLine = str_replace("\r\n", "", $fLine);

					if($fLine != ""){
						if(substr($fLine, 0, 1) != '#'){
							$username = $fLine;

							//resolves an issue
							if(!isset($_SESSION['username'])){
								unset($_SESSION['logged']);
								unset($_SESSION['username']);
								header('Location: /');
							}

							if($username == $_SESSION['username']){
								$inFile = true;
								break;
							}
						}
					}
				}
			}
			fclose($fH);
		}
	}
	else if($_SESSION['username'] == 'admin') $inFile = true;

	return $inFile;
}
?>