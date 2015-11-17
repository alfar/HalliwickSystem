<?PHP
	error_reporting(1);
	ini_set('display_errors', 1);

	// Hvilken slags database skal vi bruge?
	$dbclass = 'SqliteDB';

	// Antal baner i svømmehallen
	$number_of_tracks = 6;

	// Navn på databasens server, normalt 'localhost'
	$dbserver = '';

	// Navn på databasen
	$dbname = 'hasiStavne.db';

	// Brugernavn til databasen
	$username = '';

	// Adgangskode til databasen
	$password = '';

	// Hvor mange går videre i semifinalen ...
	$howmany_semi = array();
	
	// ... ved 4 baner ...
	$howmany_semi[4] = array();
	
	$howmany_semi[4][ 1] = 4; // ... ved 1 indledende heat.
	$howmany_semi[4][ 2] = 2; // ... ved 2 indledende heats.
	$howmany_semi[4][ 3] = 4; // ... ved 3 indledende heats.
	$howmany_semi[4][ 4] = 3; // ... ved 4 indledende heats.
	$howmany_semi[4][ 5] = 3; // ... ved 5 indledende heats.
	$howmany_semi[4][ 6] = 2; // ... ved 6 indledende heats.
	$howmany_semi[4][ 7] = 2; // ... ved 7 indledende heats.
	$howmany_semi[4][ 8] = 2; // ... ved 8 indledende heats.
	$howmany_semi[4][ 9] = 1; // ... ved 9 indledende heats.
	$howmany_semi[4][10] = 1; // ... ved 10 indledende heats.
	$howmany_semi[4][11] = 1; // ... ved 11 indledende heats.
	$howmany_semi[4][12] = 1; // ... ved 12 indledende heats.
	$howmany_semi[4][13] = 1; // ... ved 13 indledende heats.
	$howmany_semi[4][14] = 1; // ... ved 14 indledende heats.
	$howmany_semi[4][15] = 1; // ... ved 15 indledende heats.
	$howmany_semi[4][16] = 1; // ... ved 16 indledende heats.

	// ... ved 5 baner ...
	$howmany_semi[5] = array();
	
	$howmany_semi[5][ 1] = 5; // ... ved 1 indledende heat.
	$howmany_semi[5][ 2] = 2; // ... ved 2 indledende heats.
	$howmany_semi[5][ 3] = 3; // ... ved 3 indledende heats.
	$howmany_semi[5][ 4] = 3; // ... ved 4 indledende heats.
	$howmany_semi[5][ 5] = 2; // ... ved 5 indledende heats.
	$howmany_semi[5][ 6] = 3; // ... ved 6 indledende heats.
	$howmany_semi[5][ 7] = 3; // ... ved 7 indledende heats.
	$howmany_semi[5][ 8] = 3; // ... ved 8 indledende heats.
	$howmany_semi[5][ 9] = 2; // ... ved 9 indledende heats.
	$howmany_semi[5][10] = 2; // ... ved 10 indledende heats.
	$howmany_semi[5][11] = 2; // ... ved 11 indledende heats.
	$howmany_semi[5][12] = 2; // ... ved 12 indledende heats.
	$howmany_semi[5][13] = 1; // ... ved 13 indledende heats.
	$howmany_semi[5][14] = 1; // ... ved 14 indledende heats.
	$howmany_semi[5][15] = 1; // ... ved 15 indledende heats.
	$howmany_semi[5][16] = 1; // ... ved 16 indledende heats.

	// ... ved 6 baner ...
	$howmany_semi[6] = array();
	
	$howmany_semi[6][ 1] = 6; // ... ved 1 indledende heat.
	$howmany_semi[6][ 2] = 3; // ... ved 2 indledende heats.
	$howmany_semi[6][ 3] = 2; // ... ved 3 indledende heats.
	$howmany_semi[6][ 4] = 3; // ... ved 4 indledende heats.
	$howmany_semi[6][ 5] = 2; // ... ved 5 indledende heats.
	$howmany_semi[6][ 6] = 2; // ... ved 6 indledende heats.
	$howmany_semi[6][ 7] = 2; // ... ved 7 indledende heats.
	$howmany_semi[6][ 8] = 2; // ... ved 8 indledende heats.
	$howmany_semi[6][ 9] = 2; // ... ved 9 indledende heats.
	$howmany_semi[6][10] = 3; // ... ved 10 indledende heats.
	$howmany_semi[6][11] = 3; // ... ved 11 indledende heats.
	$howmany_semi[6][12] = 3; // ... ved 12 indledende heats.
	$howmany_semi[6][13] = 2; // ... ved 13 indledende heats.
	$howmany_semi[6][14] = 2; // ... ved 14 indledende heats.
	$howmany_semi[6][15] = 2; // ... ved 15 indledende heats.
	$howmany_semi[6][16] = 2; // ... ved 16 indledende heats.

	// Hvor mange går videre i finalen ...
	$howmany_final = array();
	
	// ... ved 4 baner ...
	$howmany_final[4] = array();
	
	$howmany_final[4][ 1] = 4; // ... ved 1 semifinale heat.
	$howmany_final[4][ 2] = 2; // ... ved 2 semifinale heats.
	$howmany_final[4][ 3] = 1; // ... ved 3 semifinale heats.
	$howmany_final[4][ 4] = 1; // ... ved 4 semifinale heats.

	// ... ved 5 baner ...
	$howmany_final[5] = array();
	
	$howmany_final[5][ 1] = 5; // ... ved 1 semifinale heat.
	$howmany_final[5][ 2] = 2; // ... ved 2 semifinale heats.
	$howmany_final[5][ 3] = 1; // ... ved 3 semifinale heats.
	$howmany_final[5][ 4] = 1; // ... ved 4 semifinale heats.
	$howmany_final[5][ 5] = 1; // ... ved 5 semifinale heats.

	// ... ved 6 baner ...
	$howmany_final[6] = array();
	
	$howmany_final[6][ 1] = 6; // ... ved 1 semifinale heat.
	$howmany_final[6][ 2] = 3; // ... ved 2 semifinale heats.
	$howmany_final[6][ 3] = 2; // ... ved 3 semifinale heats.
	$howmany_final[6][ 4] = 1; // ... ved 4 semifinale heats.
	$howmany_final[6][ 5] = 1; // ... ved 5 semifinale heats.
	$howmany_final[6][ 6] = 1; // ... ved 6 semifinale heats.

	// Hvilke slags diplomer skal vi kunne printe?
	$diplomaTypes = array();
	$diplomaTypes['HASI'] = 'HASI fortrykt diplom';
//	$diplomaTypes['Sælungerne'] = 'Sælungerne fortrykt diplom';

	$locale = "da_DK";

	//-------------------------------------------------------
	// Herunder skal der som sådan ikke pilles.
	//-------------------------------------------------------	
	require_once($dbclass . '.php');

	putenv("LC_ALL=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", "./translations");
	textdomain("messages");
?>