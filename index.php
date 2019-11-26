<?php
//initializing time limit to more than 5 mins just to avoid 30 seonds default execution time
ini_set('max_execution_time', 1000);
set_time_limit(1000);

//storing the first generic URL of the website to be hit for operations
$url = 'https://www.imdb.com/chart/moviemeter';

//#Set CURL parameters to fetch the data using php cURL !!!!
$ch = curl_init();
curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_PROXY, '');

$data = curl_exec($ch);
curl_close($ch);

//getting the contents of the DOM page and loading it (process of the core php)
$dom = new \DOMDocument();
@$dom->loadHTML($data);

//using xpath language to get the HTML page
$xpath = new \DOMXPath($dom);

//storing the links in movie names on first page in variable 
$movie_names = $xpath->query('//td[@class="titleColumn"]/a/@href');

$jsonVar = "";
//first loop to run through all the movies one by one by hitting the href link
foreach ($movie_names as $movie) {
	//creating a dynamic URL based on the links of every movie
	$url2 = 'www.imdb.com'.$movie->nodeValue;
	
	//running the cURL request for each movie alone to scrape the data one by one
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url2);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_PROXY, '');

	$data = curl_exec($ch);
	curl_close($ch);

	//declaring all the arrays necessary for future purpose
	$replaceReview = array();
	$splitCrtics = array();
	$splitPopularity = array();
	$splitRank = array();
	$splitBudget = array();
	$dataArr = array();

	//The function parses the HTML contained in the string source
	$dom = new \DOMDocument();
	@$dom->loadHTML($data);

	$xpath = new \DOMXPath($dom);

	//scraping all the necessary data from elements in HMTL page and saving in variables
	$movieName = $xpath->query('//div[@class="title_wrapper"]/h1');
	$movieGenre = $xpath->query('//div[@class="title_wrapper"]/div[1]//a[1]');
	$movieCritics = $xpath->query('//div[@class="titleReviewBarItem titleReviewbarItemBorder"]');
	$movieNumber = $xpath->query('//div[@class="titleReviewBarSubItem"]/div[2]');
	$movieBudget = $xpath->query('//div[@class="txt-block"][7]');
	$moviePositionUp = $xpath->query('//div[@class="titleReviewBarItem"]/div[2]//div[2]
									  /span[1]//span[@class="popularityUpOrFlat"]');
	$moviePositionDown = $xpath->query('//div[@class="titleReviewBarItem"]/div[2]//div[2]
									    /span[1]//span[@class="popularityDown"]');
	
	/*
	loops to loop through every element's data one by one
	to fetch the data required for the json file and store
	them in an array that will be encoded into json format
	and later stored in a file by the name of file.json
	*/
	foreach ($movieName as $mname) 
	{	
		$tempArr = array();
		$tempArr["Name"] = $mname->nodeValue;

		foreach ($movieCritics as $mcritics) 
		{
			foreach ($moviePositionDown as $pos) 
			{
				if($pos->nodeValue){
					$tempArr["Down"] = $pos->nodeValue;
				}
			}
			
			$splitCrtics = filterArrCritics($mcritics);
			$tempArr["TotalUserReviews"] = trim($splitCrtics[0]);
			$tempArr["TotalCriticsrReviews"] = trim($splitCrtics[1]);

			foreach ($movieGenre as $mgenre) 
			{
				$tempArr["Genre"] = $mgenre->nodeValue;

				foreach ($movieNumber as $mnumber) 
				{
					$splitRank = (filterPosition($mnumber));
					$tempArr["Rank"] = trim($splitRank[0]);
					
					foreach ($moviePositionUp as $positionup) 
					{
						$tempArr["Up"] = $positionup->nodeValue;

						foreach ($movieBudget as $mbudget) 
						{
							$splitBudget = (filterBudget($mbudget));
							$tempArr["Budget"] = trim($splitBudget[0]);
						};
					};
					
				};
			};
		};
		//pushing the key value array for json into a new array to avoid any format issues
		array_push($dataArr, $tempArr);
	};
	echo "<pre>";
	print_r($dataArr);
	
	//code to store the fetched data into a new file by the name of file.json
	$jsonVar = $jsonVar.json_encode($dataArr);
	$jsonVar = str_replace("][", ",", $jsonVar);
	file_put_contents("file.json", $jsonVar);

}

/*	function to filter User Reviews from Critics Reviews from a string 
	and return both as an array with one user reviews at 0 index and critics 
	at 1 index of the array
*/
function filterArrCritics($mcritics)
{
	$replaceReview =  str_replace("Reviews" , "", $mcritics->nodeValue);
	$splitCrtics = explode("|", $replaceReview);
	return $splitCrtics;
}

/*
	function to get the position alone 
*/
function filterPosition($mnumber)
{
	$splitPopularity = str_replace("From metacritic.com" , " ", $mnumber->nodeValue);
	$ssp = (explode("(", $splitPopularity));
	return $ssp;
}

/* 
	function to separate the budget alone from the other values that are received
	as a string in the element. 
*/
function filterBudget($mbudget)
{
	$sps = str_replace(":", "", $mbudget->nodeValue);		
	$splitBudget = explode("(" , $sps);
	$sps = str_replace("Budget", "", $splitBudget);
	return $sps;
}

