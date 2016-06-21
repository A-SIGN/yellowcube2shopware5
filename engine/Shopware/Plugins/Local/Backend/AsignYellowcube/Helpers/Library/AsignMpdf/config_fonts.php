<?php


// Optionally define a folder which contains TTF fonts
// mPDF will look here before looking in the usual _YCMPDF_TTFONTPATH
// Useful if you already have a folder for your fonts
// e.g. on Windows: define("_MPDF_SYSTEM_TTFONTS", 'C:/Windows/Fonts/');
// Leave undefined if not required

// define("_MPDF_SYSTEM_TTFONTS", '');


// Optionally set font(s) (names as defined below in $this->fontdata) to use for missing characters
// when using useSubstitutions. Use a font with wide coverage - dejavusanscondensed is a good start
// only works using subsets (otherwise would add very large file)
// doesn't do Indic or arabic
// More than 1 font can be specified but each will add to the processing time of the script

$this->backupSubsFont = array('dejavusanscondensed');


// Optionally set a font (name as defined below in $this->fontdata) to use for CJK characters
// in Plane 2 Unicode (> U+20000) when using useSubstitutions. 
// Use a font like hannomb or sun-extb if available
// only works using subsets (otherwise would add very large file)
// Leave undefined or blank if not not required

// $this->backupSIPFont = 'sun-extb';


/*
This array defines translations from font-family in CSS or HTML
to the internal font-family name used in mPDF. 
Can include as many as want, regardless of which fonts are installed.
By default mPDF will take a CSS/HTML font-family and remove spaces
and change to lowercase e.g. "Arial Unicode MS" will be recognised as
"arialunicodems"
You only need to define additional translations.
You can also use it to define specific substitutions e.g.
'frutiger55roman' => 'arial'
Generic substitutions (i.e. to a sans-serif or serif font) are set 
by including the font-family in $this->sans_fonts below
To aid backwards compatability some are included:
*/
$this->fonttrans = array(
	'helvetica' => 'chelvetica',
	'verdana' => 'arial',
	'times' => 'timesnewroman',
	'courier' => 'couriernew',
	'trebuchet' => 'arial',
	'comic' => 'arial',
	'franklin' => 'arial',
	'albertus' => 'arial',
	'arialuni' => 'arial',
	'zn_hannom_a' => 'arial',
	'ocr-b' => 'ocrb',

);

/*
This array lists the file names of the TrueType .ttf or .otf font files
for each variant of the (internal mPDF) font-family name.
['R'] = Regular (Normal), others are Bold, Italic, and Bold-Italic
Each entry must contain an ['R'] entry, but others are optional.
Only the font (files) entered here will be available to use in mPDF.
Put preferred default first in order.
This will be used if a named font cannot be found in any of 
$this->sans_fonts, $this->serif_fonts or $this->mono_fonts

['indic'] = true; for special mPDF fonts containing Indic characters
['sip-ext'] = 'hannomb'; name a related font file containing SIP characters

If a .ttc TrueType collection file is referenced, the number of the font
within the collection is required. Fonts in the collection are numbered 
starting at 1, as they appear in the .ttc file e.g.
	"cambria" => array(
		'R' => "cambria.ttc",
		'B' => "cambriab.ttf",
		'I' => "cambriai.ttf",
		'BI' => "cambriaz.ttf",
		'TTCfontID' => array(
			'R' => 1,	
			),
		),
	"cambriamath" => array(
		'R' => "cambria.ttc",
		'TTCfontID' => array(
			'R' => 2,	
			),
		),
*/

$this->fontdata = array(
	"arial" => array(
		'R' => "arial.ttf",
		'B' => "arialbd.ttf",
		'I' => "ariali.ttf",
		'BI' => "arialbi.ttf",
		),
	"couriernew" => array(
		'R' => "cour.ttf",
		'B' => "courbd.ttf",
		'I' => "couri.ttf",
		'BI' => "courbi.ttf",
		),
	"georgia" => array(
		'R' => "georgia.ttf",
		'B' => "georgiab.ttf",
		'I' => "georgiai.ttf",
		'BI' => "georgiaz.ttf",
		),
	"timesnewroman" => array(
		'R' => "times.ttf",
		'B' => "timesbd.ttf",
		'I' => "timesi.ttf",
		'BI' => "timesbi.ttf",
		),
	"verdana" => array(
		'R' => "verdana.ttf",
		'B' => "verdanab.ttf",
		'I' => "verdanai.ttf",
		'BI' => "verdanaz.ttf",
		)
);


// Add fonts to this array if they contain characters in the SIP or SMP Unicode planes
// but you do not require them. This allows a more efficient form of subsetting to be used.
$this->BMPonly = array(
	"dejavusanscondensed",
	"dejavusans",
	"dejavuserifcondensed",
	"dejavuserif",
	"dejavusansmono",
	);

// These next 3 arrays do two things:
// 1. If a font referred to in HTML/CSS is not available to mPDF, these arrays will determine whether
//    a serif/sans-serif or monospace font is substituted
// 2. The first font in each array will be the font which is substituted in circumstances as above
//     (Otherwise the order is irrelevant)
// Use the mPDF font-family names i.e. lowercase and no spaces (after any translations in $fonttrans)
// Always include "sans-serif", "serif" and "monospace" etc.
$this->sans_fonts = array('dejavusanscondensed','dejavusans','freesans','liberationsans','sans','sans-serif','cursive','fantasy', 
				'arial','helvetica','verdana','geneva','lucida','arialnarrow','arialblack','arialunicodems',
				'franklin','franklingothicbook','tahoma','garuda','calibri','trebuchet','lucidagrande','microsoftsansserif',
				'trebuchetms','lucidasansunicode','franklingothicmedium','albertusmedium','xbriyaz'

);

$this->serif_fonts = array('dejavuserifcondensed','dejavuserif','freeserif','liberationserif','serif',
				'times','timesnewroman','centuryschoolbookl','palatinolinotype','centurygothic',
				'bookmanoldstyle','bookantiqua','cyberbit','cambria',
				'norasi','charis','palatino','constantia','georgia','albertus','xbzar'
);

$this->mono_fonts = array('dejavusansmono','freemono','liberationmono','courier', 'mono','monospace','ocrb','ocr-b','lucidaconsole',
				'couriernew','monotypecorsiva'
);

?>