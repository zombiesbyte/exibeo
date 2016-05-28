Exibeo Ver 1.0

Hi and welcome to exibeo (latin for display or show).

I developed this simple app because I couldn't find any others out there
that had a simplistic easy to use (and setup) functionality.

exibeo requires very little setup and can run on a basic LAMP/WAMP/MAMP
environment. It doesn't require any database connections and just reads
files to gather information such as viewing rights and captions. I've
tried to make this a flexible (and passive) as possible.

Please note that albums contained within the photos folder should only be
1 level deep. 'captions', 'movies' and 'thumbs' folders are not required
however having and using these options can provide a greater user experience.

Folder naming should be kept to letters and numbers (no spaces) but using
a dash/hyphen symbol '-' for word breaking will be replaced with a space
within the app. Also note that all words are given a captial at the start
within the app so you can use all lowercase folder names within the structure.

It is advised that you read the readme.txt files within the 'thumbs' and
'captions' folder before use and the users/login.txt file also has notes
included for use of setting up logins.

admin is a special login which opens a few more features up including managing
users and rights files which control the user rights to viewing certain
albums.

This project is protected under general MTT guidelines shown in the license.txt
file. In short; you may use, distrobute, change, resell so long as the original
license holder is acknowledged and the existing license integrity is protected.

Setup instructions

I used a simple XAMPP install on Windows for my development environment before I
setup on Lubuntu Server. XAMPP is the easiest to describe which can be translated
for other Linux server setups.

Within C:\XAMPP\apache\conf\extra\httpd-vhosts.conf you need to include the
following code block:

<VirtualHost exibeo.home:80>
    ServerName exibeo.home
    DocumentRoot "C:/XAMPP/htdocs/exibeo/"
    ErrorLog "C:/XAMPP/htdocs/exibeo/logs/error.log"
    CustomLog "C:/XAMPP/htdocs/exibeo/logs/access.log" common
    <Directory "C:/XAMPP/htdocs/exibeo/">
       Options FollowSymLinks
       AllowOverride None
    </Directory>
    RewriteEngine On
    RewriteRule ^/(index\.php|core\.*|photos\.*).* - [s=1]
    RewriteRule ^/(.*)$ /index.php/$1
</VirtualHost>

Within your hosts file in C:\Windows\System32\drivers\etc\hosts you may need to
make sure you can write to this file (most security software stops anything from
making changes to this file but there should be a setting to allow you temporaty
access). The following entry needs to be included:

127.0.0.1	exibeo.home

If you want other devices to access this on your network then you will need to
include a similar line in their host files:

<your PC internal IP>	exibeo.home

To find your internal IP address, use ipconfig command from Windows Command Line
(cmd). You'll also need to make this a static IP by setting your computer up within
the router or within Windows (although the router method is more reliable).

Please note: exibeo.home can be changed to anything you like (just use something
that isn't going to interfer with external websites i.e. don't use google.com :D)

I'm going to presume that most of this information is verbos but I include it to
anyway. Linux and Mac setups are outside the scope of this documentation but there
should be enough here to align them to this setup.

---

General FAQ (I'll admit it, I made these up...)

Q: 	My photos seem to take a long time loading in on the album view even though
	I have setup thumbnails at the recommended sizes. (I am hosting on Linux)

A:	Linux is a caSe-SenSiTive environment (this is a good thing) but may cause
	issues if you've followed the video tutorial for how to create a batch of
	thumbs as the extension casing is not preserved. Please check that your
	thumbnail extensions match the casing of your photos:
	i.e. /myImage.JPG >> /thumbs/myImage.jpg will not match
	
	In this case you need to use something like ren *.jpg *.JPG in windows
	command line within the thumbs directory or similar for linux


Q:	What movie types can I include in my 'movies' folder?

A:	The movies supported are generally the HTML5 <video> tag list but
	depending on how these are encoded can make a difference. I used Windows
	Movie Maker to re-save them under a 'YouTube' type export under Windows 10.
	This application is from Windows 7 I believe and can be installed using
	the Windows Esential Package:
	
	http://windows.microsoft.com/en-gb/windows/essentials


Q:	How can I order my albums and create sections with titles?

A:	Within the /docs/templates folder you should have a folder called photos.
	This holds a file called list.php. Copy this to your photos folder and
	include all the albums you wish to make available (if a album folder name is
	excluded from this list, not even admin will see it). There is further
	instructions included in list.php to explain how to add section titles and
	even a emoticon character.


Q:	Is this a secure area for my photos?

A:	The intention of this web app was to present a collection of albums to a user
	based on their login information. Although the albums that are not included
	for a particular login are hidden, it is still possible for anyone to access
	the folder should they have to tools and know-how to retreive information from
	a directory scan. In short, generally your photos won't be found but they
	would not be secure. This will be a greater concern if this is going to be a
	public server.

Q:	What Emoticons can I use?

A:	There are a number of Emoticons included with the project. These can be used by
	typing <emot class='{popo-class-name-here}'></emot> the following classes apply:
	
	popo-blacy-amazing, popo-blacy-anger, popo-blacy-bad-egg, popo-blacy-bad-smile,
	popo-blacy-beaten, popo-blacy-big-smile, popo-blacy-black-heart, popo-blacy-cry,
	popo-blacy-electric-shock, popo-blacy-exciting, popo-blacy-eyes-droped,
	popo-blacy-girl, popo-blacy-greedy, popo-blacy-grimace, popo-blacy-haha,
	popo-blacy-happy, popo-blacy-horror, popo-blacy-money, popo-blacy-nothing,
	popo-blacy-nothing-to-say, popo-blacy-red-heart, popo-blacy-scorn,
	popo-blacy-secret-smile, popo-blacy-shame, popo-blacy-shocked,
	popo-blacy-super-man, popo-blacy-the-iron-man, popo-blacy-unhappy,
	popo-blacy-victory, popo-blacy-what, popo-yellow-adore, popo-yellow-after-boom,
	popo-yellow-ah, popo-yellow-amazed, popo-yellow-angry, popo-yellow-bad-smelly,
	popo-yellow-baffle, popo-yellow-beated, popo-yellow-beat-brick,
	popo-yellow-beat-plaster, popo-yellow-beat-shot, popo-yellow-beauty,
	popo-yellow-big-smile, popo-yellow-boss, popo-yellow-burn-joss-stick,
	popo-yellow-byebye, popo-yellow-canny, popo-yellow-choler, popo-yellow-cold,
	popo-yellow-confident, popo-yellow-confuse, popo-yellow-cool, popo-yellow-cry,
	popo-yellow-doubt, popo-yellow-dribble, popo-yellow-embarrassed,
	popo-yellow-extreme-sexy-girl, popo-yellow-feel-good, popo-yellow-go,
	popo-yellow-haha, popo-yellow-hell-boy, popo-yellow-hungry,
	popo-yellow-look-down, popo-yellow-matrix, popo-yellow-misdoubt,
	popo-yellow-nosebleed, popo-yellow-oh, popo-yellow-ops, popo-yellow-pudency,
	popo-yellow-rap, popo-yellow-sad, popo-yellow-sexy-girl, popo-yellow-shame,
	popo-yellow-smile, popo-yellow-spiderman, popo-yellow-still-dreaming,
	popo-yellow-sure, popo-yellow-surrender, popo-yellow-sweat,
	popo-yellow-sweet-kiss, popo-yellow-tire, popo-yellow-too-sad,
	popo-yellow-waaaht, popo-yellow-what

	Thanks to:
	POPO Emoticons - Copyright & Reserved by Netease
	[Author: Rokey | Website: www.rokey.net]
	For making these available for use (non-commerical)

	These icons can be found in \exibeo\core\emoticons


Q:	What browsers does this app support

A:	Although Mozilla FireFox was my preferred browser, I have tested (and made small
	changes) for other browsers. Please let me know if you spot any bugs.



More to follow bases on real people asking real questions :)

Many thanks.
James Dalgarno
james (at) zombiesbyte (dot) com