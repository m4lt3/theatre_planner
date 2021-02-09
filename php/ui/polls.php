<?php
function createPollCard($poll){
  global $db;
  global $lang;

  $date = date_create($poll["Start"]);
  $startFormat = date_format($date, "d.m.Y");
  date_add($date, date_interval_create_from_date_string(($poll["Duration"]-1)." days"));
  $endFormat = date_format($date, "d.m.Y");

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
        <a href="dates.php?poll={$poll["PollID"]}">{$lang->poll} #{$poll["PollID"]}</a>
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

function createTRow($name, $entries, $editable, $duration, $uid){
  global $lang;

  $parsed_entries = decbin((int)$entries);
  if(strlen($parsed_entries)<$duration){
    $parsed_entries = str_repeat("0", $duration - strlen($parsed_entries)) . $parsed_entries;
  }
  $row = "<tr>";
  if($editable){
    $row .= '<form action="" method="post">';
  }
  $row .= "<td>".$name."</td>";
  if(!isset($entries)){
    $parsed_entries = str_repeat("0", $duration);
  }

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
    $row .= '<td><input type="hidden" name="uid" value="'.$uid.'"><button type="submit" class="ui primary button">'.$lang->save.'</button></td></form>';
  } else {
    $row .= '<td></td>';
  }
  $row .= "</tr>";
  return $row;
}

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
?>
