<?php
/**
* Creades a Card containing all relevant information about a poll.
*
* @param array $poll associative array containing the following information about the poll: ID, Start, DUration, Description
*
* @return string card template
*/
function createPollCard($poll){
  global $db;
  global $lang;

  //Creating start and end date formats
  $date = date_create($poll["Start"]);
  $startFormat = date_format($date, "d.m.Y");
  date_add($date, date_interval_create_from_date_string(($poll["Duration"]-1)." days"));
  $endFormat = date_format($date, "d.m.Y");

  // Determining whether the user has already participated in the poll
  $participated = "";
  if(!empty($db->prepareQuery("SELECT EntryID FROM POLL_ENTRIES WHERE PollID=? AND UserID=?","ii", array($poll["PollID"],$_SESSION["UserID"])))){
    $participated='<div class="ui green label">'.$lang->participated.'</div>';
  } else {
    $participated='<div class="ui red label">'.$lang->not_participated.'</div>';
  }

  $card = <<<EOT
  <div class="ui card">
    <div class="content">
      <div class="header">
        <a href="polls.php?poll={$poll["PollID"]}">{$lang->poll} #{$poll["PollID"]}</a>
      </div>
    </div>
    <div class="content">
      <div class="ui sub header">{$lang->timeframe}</div>
      $startFormat - $endFormat
    </div>
    <div class="content">
      <div class="ui sub header">
        {$lang->description}
      </div>
      {$poll["Description"]}
    </div>
    <div class="extra content" style="text-align:center">
      $participated
    </div>
  </div>
EOT;
  return $card;
}

/**
* Creates the table head of the date poll.
*
* @param string $start date string of the start date of the date poll
* @param int $duration The amount of days after the start date to be covered by this poll
*
* @return string table head template
*/
function createTHead($start, $duration){
  global $lang;

  $head = '<th>'.$lang->name.'</th>';
  $date = date_create($start);
  for($i = 0; $i < $duration; $i++){
    $head .= "<th>".date_format($date, "d.m.Y")."</th>";
    date_add($date, date_interval_create_from_date_string("1 days"));
  }
  $head .= '<th>'.$lang->save.'</th>';
  return $head;
}

/**
* Creates a row in the table; i.e. a user and all selections
*
* @param string $name Name of the user
* @param int $entries Bitmap of selected dates. The leftmost bit represents the start date, the next bit represents the day after and so on.
* @param boolean $editable whether the row is read-only or if the user can change values
* @param int $duration The amount of days after the start date to be covered by this poll - i.e. how many cells are generated
* @param int|string $uid The userID the row "belongs" to
*
* @return string row template
*/
function createTRow($name, $entries, $editable, $duration, $uid){
  global $lang;

  // converting int to bitmap; prefixing zeroes if necessary (or not set at all)
  if(!isset($entries)){
    $parsed_entries = str_repeat("0", $duration);
  } else {
    $parsed_entries = decbin((int)$entries);
    if(strlen($parsed_entries)<$duration){
      $parsed_entries = str_repeat("0", $duration - strlen($parsed_entries)) . $parsed_entries;
    }
  }


  $row = "<tr>";
  if($editable){
    $row .= '<form action="" method="post">';
  }
  $row .= "<td>".$name."</td>";
  if($editable){
    for($i = 0; $i < $duration; $i++){
      if($parsed_entries[$i]==1){
        $row .= '<td class="positive"><div class="ui checkbox"><input type="checkbox" name="'.$i.'" checked></div></td>';
      } else {
        $row .= '<td class="negative"><div class="ui checkbox"><input type="checkbox" name="'.$i.'"></div></td>';
      }
    }
  } else {
    for($i = 0; $i < $duration; $i++){
      if($parsed_entries[$i]==1){
        $row .= '<td class="positive"><i class="check icon"></i></td>';
      } else {
        $row .= '<td class="negative"><i class="close icon"></i></td>';
      }
    }
  }

  if($editable){
    $row .= '<td><input type="hidden" name="uid" value="'.$uid.'"><input type="submit" class="ui primary button" name="change_entries" value="'.$lang->save.'"></td></form>';
  } else {
    $row .= '<td></td>';
  }
  $row .= "</tr>";
  return $row;
}

/**
* Creates a row that sums up all votes
*
* @param array $entries array of all entries of all users
* @param int $duration The amount of days after the start date to be covered by this poll - i.e. how many cells are generated
*
* @return string sum row template
*/
function createSumRow($entries, $duration){
  global $lang;

  $matrix = array();
  // filling matrix
  for($i = 0; $i < $duration; $i++){
    if(!isset($entries[$i]["Entries"])){
      $matrix[] = str_repeat("0", $duration);
    } else {
      $parsed_entries = decbin($entries[$i]["Entries"]);
      if(strlen($parsed_entries)<$duration){
        $parsed_entries = str_repeat("0", $duration - strlen($parsed_entries)) . $parsed_entries;
      }
      $matrix[] = $parsed_entries;
    }
  }

  //summing up columns
  $sums = array();
  for($i = 0; $i < $duration; $i++){
    $sum = 0;
    for($j = 0; $j < count($matrix); $j++){
      $sum += (int) $matrix[$j][$i];
    }
    $sums[] = $sum;
  }

  $row = '<tr><td><b>'.$lang->sum.'</b></td>';

  $max = max($sums);
  for($i = 0; $i < $duration; $i++){
    $row .= '<td'.($sums[$i]==$max?' class="positive"':'').'>'.($sums[$i]==$max?'<b style="font-size:130%">':'').$sums[$i].($sums[$i]==$max?'</b>':'').'</td>';
  }
  $row .= "<td></td></tr>";
  return $row;
}

/**
* Creates buttons to add a practice on the coresponding poll option
*
* @param int $duration The amount of days after the start date to be covered by this poll - i.e. how many buttons are generated
* @param string $start date string of the start date of the date poll
* @param array $practices the dates of all future practices to check if there already is a coresponding practice
*
* @return string template for button rows
*/
function createButtons($duration, $start, $practices){
  global $lang;

  // generating map for better runtime
  $pmap = array();
  foreach ($practices as $practice) {
    $pmap[$practice["Start"]] = true;
  }

  $date = date_create($start);
  $foot = '<tfoot><tr><td></td>';
  for($i = 0; $i < $duration; $i++){
    if(isset($pmap[date_format($date,"Y-m-d")])){
      $foot .= '<td style="text-align:center"><button type="button" class="ui disabled primary button">'.$lang->already_added.'</button>';
    } else {
      $foot .= '<td style="text-align:center"><form action="" method="post"><input type="hidden" name="col" value ="'.$i.'"><input type="submit" class="ui primary button" name="create_practice" value="'.$lang->add_date.'"></form></td>';
    }
    date_add($date, date_interval_create_from_date_string("1 days"));
  }
  $foot .= '<td></td></tfoot>';
  return $foot;
}
?>
