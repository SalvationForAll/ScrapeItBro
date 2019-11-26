<?php

//fetching the data from json file
$strr = file_get_contents("file.json");
$jsonArr = json_decode($strr, true);

//all the necessary variables and arrays used in the program
$up = 'Up';
$down = 'Down';
$storeGenre = array();
$storeName = array();
$fileHeader1 = array(
	'Movies with Improved Popularity',
);
$fileHeader2 = array(
	'Previous Week Position',
);
$fileHeader3 = array(
	'Highest Rated Genre',
);

//creating csv for movies that have improved in popularity with storing header 
$fp = fopen('ImprovedPopularity.csv', 'w');
fputcsv($fp, $fileHeader1);

//searching movies that have improved in popularity and saving in csv
foreach ($jsonArr as $key => $value) {
	if(isset($value[$up])){
		echo $value['Name'];
		echo "<br>";
		fputcsv($fp, array($value['Name']));
	}
}
fclose($fp);


echo"<br>";
echo"<br>";
echo"<br>";

//creating csv for movies and their respective previous week's positions with storing header 
$fp = fopen('PreviousWeek.csv', 'w');
fputcsv($fp, $fileHeader2);
//previous week's position of each movie
foreach ($jsonArr as $key => $value) {
	if(isset($value[$up])){
		$prevWeekRank = $value['Rank'] + $value['Up'];
		echo $value['Name'] . "<br> Previous Week Position: " . $prevWeekRank . "<br><br>";
		fputcsv($fp, array( $value['Name'], $prevWeekRank));
	}
	if(isset($value[$down])){
		$prevWeekRank = $value['Rank'] - $value['Down'];
		echo $value['Name'] . "<br> Previous Week Position: " . $prevWeekRank . "<br><br>";
		fputcsv($fp, array( $value['Name'], $prevWeekRank));
	}
}
fclose($fp);

echo "<br>";
echo "<br>";
echo "<br>";

//creating csv for highest rated Genre with storing header 
$fp = fopen('HighestGenre.csv', 'w');
fputcsv($fp, $fileHeader3);
//highest rated genre ~ array_column function extracts only given column values 
$storeGenre = array_column($jsonArr, 'Genre');
$n = sizeof($storeGenre); 
echo "Highest rated Genre: ";
echo $ss = mostFrequent($storeGenre, $n);
fputcsv($fp, array($ss));

//function to calculate the average of genre
function mostFrequent($arr, $n) 
{       
    // Sort the array 
    sort($arr); 
  
    // find the max frequency  
    $max_count = 1;  
    $res = $arr[0];  
    $curr_count = 1; 
    for ($i = 1; $i < $n; $i++)  
    { 
        if ($arr[$i] == $arr[$i - 1]) 
            $curr_count++; 
        else 
        { 
            if ($curr_count > $max_count) 
            { 
                $max_count = $curr_count; 
                $res = $arr[$i - 1]; 
            } 
            $curr_count = 1; 
        } 
    } 
  
    // If last element is most frequent 
    if ($curr_count > $max_count) 
    { 
        $max_count = $curr_count; 
        $res = $arr[$n - 1]; 
    } 
  
    return $res; 
} 
  
